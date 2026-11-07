const QR_SIZE = 21;
const QR_DATA_CODEWORDS = 19;
const QR_ECC_CODEWORDS = 7;

function gfMultiply(left, right) {
    let result = 0;
    for (let bit = 7; bit >= 0; bit -= 1) {
        result = (result << 1) ^ ((result >>> 7) * 0x11d);
        result ^= ((right >>> bit) & 1) * left;
    }
    return result;
}

function errorCorrection(data, degree) {
    let generator = [1];
    let root = 1;

    for (let i = 0; i < degree; i += 1) {
        const next = Array(generator.length + 1).fill(0);
        generator.forEach((coefficient, index) => {
            next[index] ^= coefficient;
            next[index + 1] ^= gfMultiply(coefficient, root);
        });
        generator = next;
        root = gfMultiply(root, 2);
    }

    const remainder = Array(degree).fill(0);
    for (const byte of data) {
        const factor = byte ^ remainder[0];
        remainder.shift();
        remainder.push(0);
        for (let i = 0; i < degree; i += 1)
            remainder[i] ^= gfMultiply(generator[i + 1], factor);
    }
    return remainder;
}

function memberQrMatrix(value) {
    const bytes = Array.from(new TextEncoder().encode(value));
    if (bytes.length > 17)
        throw new Error("Kode member melebihi kapasitas QR.");

    const bits = [];
    const appendBits = (number, length) => {
        for (let bit = length - 1; bit >= 0; bit -= 1)
            bits.push((number >>> bit) & 1);
    };
    appendBits(0b0100, 4); // Byte mode
    appendBits(bytes.length, 8); // Version 1 character count
    bytes.forEach((byte) => appendBits(byte, 8));
    const capacity = QR_DATA_CODEWORDS * 8;
    appendBits(0, Math.min(4, capacity - bits.length));
    while (bits.length % 8) bits.push(0);

    const data = [];
    for (let i = 0; i < bits.length; i += 8) {
        data.push(
            bits.slice(i, i + 8).reduce((value, bit) => (value << 1) | bit, 0),
        );
    }
    for (let pad = 0; data.length < QR_DATA_CODEWORDS; pad += 1)
        data.push(pad % 2 ? 0x11 : 0xec);
    const codewords = [...data, ...errorCorrection(data, QR_ECC_CODEWORDS)];

    const matrix = Array.from({ length: QR_SIZE }, () =>
        Array(QR_SIZE).fill(null),
    );
    const setFunction = (x, y, dark) => {
        if (x >= 0 && x < QR_SIZE && y >= 0 && y < QR_SIZE)
            matrix[y][x] = Boolean(dark);
    };

    const finder = (centerX, centerY) => {
        for (let dy = -4; dy <= 4; dy += 1) {
            for (let dx = -4; dx <= 4; dx += 1) {
                const distance = Math.max(Math.abs(dx), Math.abs(dy));
                setFunction(
                    centerX + dx,
                    centerY + dy,
                    distance !== 2 && distance !== 4,
                );
            }
        }
    };
    finder(3, 3);
    finder(QR_SIZE - 4, 3);
    finder(3, QR_SIZE - 4);

    for (let index = 8; index < QR_SIZE - 8; index += 1) {
        setFunction(6, index, index % 2 === 0);
        setFunction(index, 6, index % 2 === 0);
    }

    // Reserve the format-information modules and the fixed dark module.
    for (let index = 0; index < 15; index += 1) {
        const verticalY =
            index < 6 ? index : index < 8 ? index + 1 : QR_SIZE - 15 + index;
        const horizontalX =
            index < 8 ? QR_SIZE - 1 - index : index < 9 ? 7 : 14 - index;
        setFunction(8, verticalY, false);
        setFunction(horizontalX, 8, false);
    }
    setFunction(8, QR_SIZE - 8, true);

    let bitIndex = 0;
    for (let right = QR_SIZE - 1; right >= 1; right -= 2) {
        if (right === 6) right -= 1;
        const upward = ((right + 1) & 2) === 0;
        for (let vertical = 0; vertical < QR_SIZE; vertical += 1) {
            const y = upward ? QR_SIZE - 1 - vertical : vertical;
            for (let offset = 0; offset < 2; offset += 1) {
                const x = right - offset;
                if (matrix[y][x] !== null) continue;
                const rawBit =
                    bitIndex < codewords.length * 8
                        ? (codewords[bitIndex >>> 3] >>> (7 - (bitIndex & 7))) &
                          1
                        : 0;
                bitIndex += 1;
                matrix[y][x] = Boolean(rawBit ^ ((x + y) % 2 === 0 ? 1 : 0)); // Mask 0
            }
        }
    }

    const formatData = (0b01 << 3) | 0; // Error correction level L, mask 0
    let remainder = formatData << 10;
    for (let bit = 14; bit >= 10; bit -= 1) {
        if ((remainder >>> bit) & 1) remainder ^= 0x537 << (bit - 10);
    }
    const format = ((formatData << 10) | remainder) ^ 0x5412;
    for (let index = 0; index < 15; index += 1) {
        const dark = ((format >>> index) & 1) !== 0;
        const verticalY =
            index < 6 ? index : index < 8 ? index + 1 : QR_SIZE - 15 + index;
        const horizontalX =
            index < 8 ? QR_SIZE - 1 - index : index < 9 ? 7 : 14 - index;
        matrix[verticalY][8] = dark;
        matrix[8][horizontalX] = dark;
    }
    matrix[QR_SIZE - 8][8] = true;

    return matrix;
}

function drawMemberQr(canvas) {
    const value = canvas.dataset.qrValue?.trim();
    if (!value) return;
    try {
        const matrix = memberQrMatrix(value);
        const quietZone = 4;
        const scale = 8;
        const dimension = (QR_SIZE + quietZone * 2) * scale;
        canvas.width = dimension;
        canvas.height = dimension;
        const context = canvas.getContext("2d", { alpha: false });
        context.fillStyle = "#fffefa";
        context.fillRect(0, 0, dimension, dimension);
        context.fillStyle = "#151412";
        matrix.forEach((row, y) =>
            row.forEach((dark, x) => {
                if (dark)
                    context.fillRect(
                        (x + quietZone) * scale,
                        (y + quietZone) * scale,
                        scale,
                        scale,
                    );
            }),
        );
    } catch {
        canvas.hidden = true;
    }
}

const card = document.querySelector("#loyalty-card");
const flipButtons = document.querySelectorAll("[data-card-flip]");
const flipLabel = document.querySelector("[data-flip-label]");

document
    .querySelectorAll(".card-progress-fill[data-progress]")
    .forEach((fill) => {
        const progress = Number(fill.dataset.progress);
        const boundedProgress = Number.isFinite(progress)
            ? Math.min(100, Math.max(0, progress))
            : 0;
        fill.style.setProperty("--progress", `${boundedProgress}%`);
    });

document.querySelectorAll(".stamp-token[data-stamp-index]").forEach((stamp) => {
    const index = Number(stamp.dataset.stampIndex);
    if (Number.isInteger(index) && index > 0 && index <= 12) {
        stamp.style.setProperty("--stamp-index", String(index));
    }
});

flipButtons.forEach((button) =>
    button.addEventListener("click", () => {
        const flipped = card.classList.toggle("is-flipped");
        card.style.transform = "";
        flipButtons.forEach((control) =>
            control.setAttribute("aria-pressed", String(flipped)),
        );
        if (flipLabel)
            flipLabel.textContent = flipped
                ? "Lihat sisi depan"
                : "Lihat sisi belakang";
    }),
);

const finePointer = window.matchMedia("(hover: hover) and (pointer: fine)");
if (
    card &&
    finePointer.matches &&
    !window.matchMedia("(prefers-reduced-motion: reduce)").matches
) {
    card.addEventListener("pointermove", (event) => {
        if (card.classList.contains("is-flipped")) return;
        const bounds = card.getBoundingClientRect();
        const x = (event.clientX - bounds.left) / bounds.width;
        const y = (event.clientY - bounds.top) / bounds.height;
        card.style.transform = `rotateX(${(0.5 - y) * 5}deg) rotateY(${(x - 0.5) * 7}deg)`;
        card.style.setProperty("--light-x", `${x * 100}%`);
        card.style.setProperty("--light-y", `${y * 100}%`);
    });
    card.addEventListener("pointerleave", () => {
        card.style.transform = "";
    });
}

document.querySelectorAll("canvas[data-qr-value]").forEach(drawMemberQr);

const qrDialog = document.querySelector("[data-qr-dialog]");
const dialogQr = qrDialog?.querySelector("[data-qr-large]");
document.querySelector("[data-qr-open]")?.addEventListener("click", () => {
    const source = document.querySelector(".member-qr");
    if (!source || !dialogQr || !qrDialog) return;
    const context = dialogQr.getContext("2d", { alpha: false });
    dialogQr.width = source.width;
    dialogQr.height = source.height;
    context.drawImage(source, 0, 0);
    qrDialog.showModal();
});
document
    .querySelector("[data-qr-close]")
    ?.addEventListener("click", () => qrDialog?.close());
qrDialog?.addEventListener("click", (event) => {
    if (event.target === qrDialog) qrDialog.close();
});

document
    .querySelector("[data-copy-member]")
    ?.addEventListener("click", async (event) => {
        const button = event.currentTarget;
        const label = button.querySelector("[data-copy-label]");
        const value = button.dataset.copyMember || "";
        try {
            await navigator.clipboard.writeText(value);
            if (label) label.textContent = "Tersalin";
            button.setAttribute("aria-label", "Nomor member berhasil disalin");
        } catch {
            const input = document.createElement("textarea");
            input.value = value;
            input.setAttribute("readonly", "");
            input.style.position = "fixed";
            input.style.opacity = "0";
            document.body.append(input);
            input.select();
            const copied = document.execCommand("copy");
            input.remove();
            if (label) label.textContent = copied ? "Tersalin" : value;
        }
        window.setTimeout(() => {
            if (label) label.textContent = "Salin ID";
            button.setAttribute("aria-label", `Salin nomor member ${value}`);
        }, 1800);
    });

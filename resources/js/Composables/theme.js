const DEFAULT_PRIMARY = '#0B3D8C';
const DEFAULT_SECONDARY = '#64748B';

export function normalizeHex(hex, fallback = DEFAULT_PRIMARY) {
    if (!hex || typeof hex !== 'string') return fallback;
    let value = hex.trim();
    if (!value.startsWith('#')) value = `#${value}`;
    return /^#[0-9A-Fa-f]{6}$/.test(value) ? value.toUpperCase() : fallback;
}

function hexToRgb(hex) {
    const h = normalizeHex(hex).slice(1);
    return [parseInt(h.slice(0, 2), 16), parseInt(h.slice(2, 4), 16), parseInt(h.slice(4, 6), 16)];
}

function rgbToHex(r, g, b) {
    return `#${[r, g, b]
        .map((n) => Math.max(0, Math.min(255, Math.round(n))).toString(16).padStart(2, '0'))
        .join('')
        .toUpperCase()}`;
}

function mix(hex, target, t) {
    const [r, g, b] = hexToRgb(hex);
    return rgbToHex(
        r + (target[0] - r) * t,
        g + (target[1] - g) * t,
        b + (target[2] - b) * t,
    );
}

export function paletteFromHex(hex) {
    const base = normalizeHex(hex);
    return {
        50: mix(base, [255, 255, 255], 0.92),
        100: mix(base, [255, 255, 255], 0.8),
        200: mix(base, [255, 255, 255], 0.62),
        300: mix(base, [255, 255, 255], 0.42),
        400: mix(base, [255, 255, 255], 0.2),
        500: mix(base, [255, 255, 255], 0.05),
        600: base,
        700: mix(base, [0, 0, 0], 0.18),
        800: mix(base, [0, 0, 0], 0.32),
        900: mix(base, [0, 0, 0], 0.45),
    };
}

function setRgbVar(el, name, hex) {
    const [r, g, b] = hexToRgb(hex);
    el.style.setProperty(name, `${r} ${g} ${b}`);
}

export function applyTheme(theme = {}) {
    const root = document.documentElement;
    const primary = normalizeHex(theme.primary_color, DEFAULT_PRIMARY);
    const secondary = normalizeHex(theme.secondary_color, DEFAULT_SECONDARY);
    const brand = paletteFromHex(primary);
    const soft = paletteFromHex(secondary);

    Object.entries(brand).forEach(([shade, hex]) => {
        setRgbVar(root, `--brand-${shade}`, hex);
    });
    Object.entries(soft).forEach(([shade, hex]) => {
        setRgbVar(root, `--soft-${shade}`, hex);
    });

    const [pr, pg, pb] = hexToRgb(brand[500]);
    const [sr, sg, sb] = hexToRgb(soft[500]);
    root.style.setProperty(
        '--soft-mesh',
        `radial-gradient(ellipse 80% 50% at 0% -20%, rgba(${pr}, ${pg}, ${pb}, 0.14), transparent), radial-gradient(ellipse 60% 40% at 100% 0%, rgba(${sr}, ${sg}, ${sb}, 0.12), transparent), linear-gradient(180deg, rgb(var(--soft-50)) 0%, rgb(var(--soft-100)) 100%)`,
    );
}

export const THEME_PRESETS = [
    { name: 'CashierLaundry', primary_color: '#0B3D8C', secondary_color: '#64748B' },
    { name: 'Ocean Blue', primary_color: '#2563EB', secondary_color: '#64748B' },
    { name: 'Teal Soft', primary_color: '#278074', secondary_color: '#627589' },
    { name: 'Forest', primary_color: '#2F6B4F', secondary_color: '#6B7280' },
    { name: 'Slate Navy', primary_color: '#334155', secondary_color: '#78716C' },
    { name: 'Rose Soft', primary_color: '#BE5A6B', secondary_color: '#6B7280' },
    { name: 'Amber Warm', primary_color: '#B45309', secondary_color: '#57534E' },
];

export { DEFAULT_PRIMARY, DEFAULT_SECONDARY };

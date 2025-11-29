/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/Resources/**/*.blade.php",
        "./src/Resources/**/*.js",
        "./src/Resources/assets/css/tokens.css",
    ],

    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1920px",
            },

            padding: {
                DEFAULT: "16px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1920px",
        },

        extend: {
            colors: {
                brand: {
                    DEFAULT: "var(--brand-primary)",
                    strong: "var(--brand-primary-strong)",
                    medium: "var(--brand-primary-medium)",
                    soft: "var(--brand-primary-soft)",
                    softStrong: "var(--brand-primary-soft-strong)",
                },

                surface: {
                    body: "var(--surface-body)",
                    card: "var(--surface-card)",
                    muted: "var(--surface-muted)",
                    inverse: "var(--surface-inverse)",
                },

                border: {
                    default: "var(--border-default)",
                    hover: "var(--border-hover)",
                    focus: "var(--border-focus)",
                    error: "var(--border-error)",
                    muted: "var(--border-muted)",
                    strong: "var(--border-strong)",
                },

                bg: {
                    hover: "var(--bg-hover)",
                    mutedLight: "var(--bg-muted-light)",
                    badge: "var(--bg-badge)",
                },

                text: {
                    primary: "var(--text-primary)",
                    secondary: "var(--text-secondary)",
                    muted: "var(--text-muted)",
                    inverse: "var(--text-inverse)",
                    light: "var(--text-light)",
                    error: "var(--text-error)",
                    success: "var(--text-success)",
                    link: "var(--text-link)",
                },

                status: {
                    success: "var(--status-success)",
                    warning: "var(--status-warning)",
                    info: "var(--status-info)",
                    danger: "var(--status-danger)",
                },
                navyBlue: '#060C3B'

            },

            fontFamily: {
                inter: ['Inter'],
                icon: ['icomoon'],
                dmserif: ['DM Serif Display', 'serif']
            },
        },
    },

    darkMode: 'class',

    plugins: [],

    safelist: [
        {
            pattern: /icon-/,

        },
        "bg-[var(--status-success)]",
        "bg-[var(--status-info)]",
        "bg-[var(--status-warning)]",
        "bg-[var(--status-danger)]",
        "bg-[var(--status-info-bg)]",
        "bg-[var(--status-warning-bg)]",
    ]
};

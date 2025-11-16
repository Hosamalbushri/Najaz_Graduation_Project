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

                text: {
                    primary: "var(--text-primary)",
                    secondary: "var(--text-secondary)",
                    muted: "var(--text-muted)",
                    inverse: "var(--text-inverse)",
                },

                status: {
                    success: "var(--status-success)",
                    warning: "var(--status-warning)",
                    info: "var(--status-info)",
                    danger: "var(--status-danger)",
                },
            },

            fontFamily: {
                inter: ['Inter'],
                icon: ['icomoon']
            }
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

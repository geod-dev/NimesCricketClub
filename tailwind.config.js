/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		"./assets/**/*.js",
		"./templates/**/*.html.twig",
	],
	theme: {
		extend: {
			fontSize: {
				base: '1.05rem',
			},
			borderRadius: {
				DEFAULT: "0.375rem",
			},
			colors: {
				background: {
					DEFAULT: "#fff",
					dark: "#f0f2f1",
					darker: "#e6e8e7"
				},
				foreground: {
					extralight: "#b8c2bf",
					light: "#3a423f",
					DEFAULT: "#0f1512",
					dark: "#0a0f0d"
				},
				default: {
					50: "#fafafa",
					100: "#f4f4f5",
					200: "#e4e4e7",
					300: "#d4d4d8",
					400: "#a1a1aa",
					500: "#71717a",
					DEFAULT: "#71717a",
					600: "#52525b",
					700: "#3f3f46",
					800: "#27272a",
					900: "#18181b"
				},
				primary: {
					100: "#C6F8CE",
					200: "#91F1AA",
					300: "#56D784",
					400: "#2BAF68",
					500: "#007B45",
					DEFAULT: "#007B45",
					600: "#006945",
					700: "#005843",
					800: "#00473D",
					900: "#003B38"
				},
				secondary: {
					100: "#F9DED0",
					200: "#F4B9A4",
					300: "#E08471",
					400: "#C1544A",
					500: "#991b1b",
					DEFAULT: "#991b1b",
					600: "#83131D",
					700: "#6E0D1E",
					800: "#58081D",
					900: "#49051C"
				},
				divider: "rgba(17, 17, 17, 0.15)"
			}
		},
	},
	plugins: [],
}


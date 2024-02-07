export default function Navbar() {
	const navbar = document.querySelector(".navbar");
	const menu = navbar.querySelector(".menu");
	const menuOpen = navbar.querySelector(".menu-open");
	const menuClose = navbar.querySelector(".menu-close");

	menuOpen.addEventListener("click", open)
	menuClose.addEventListener("click", close)

	function open() {
		menu.classList.remove("-translate-x-full")
		menuClose.classList.remove("hidden")
	}

	function close() {
		menu.classList.add("-translate-x-full")
		menuClose.classList.add("hidden")
	}
}
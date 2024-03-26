export default function Modal() {
	const buttons = document.querySelectorAll(".modal-toggle");

	buttons.forEach(button => {
		const id = button.getAttribute("data-for")
		if (id) {
			const modal = document.getElementById(id)
			if (modal) button.addEventListener("click", () => openModal(modal))
		}
	})

	function openModal(modal) {
		modal.classList.add("active")

		const background = document.createElement("div")
		background.classList.add("modal-background")
		background.id = modal.id + "-background"
		document.body.appendChild(background)

		background.addEventListener("click", () => closeModal(modal))
	}

	function closeModal(modal) {
		modal.classList.remove("active")

		const background = document.getElementById(modal.id + "-background")
		background.removeEventListener("click", () => closeModal(modal))
		background.remove()
	}
}

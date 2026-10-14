import { Calendar } from '@fullcalendar/core'
import frLocale from '@fullcalendar/core/locales/fr'
import dayGridPlugin from '@fullcalendar/daygrid'
import listPlugin from '@fullcalendar/list'

import '../styles/calendar.scss'

document.addEventListener('DOMContentLoaded', () => {
	const element = document.querySelector('.event-calendar')
	if (!element) return

	const modal = document.getElementById('event-modal')
	const mobile = window.matchMedia('(max-width: 639px)')
	const dateFormat = new Intl.DateTimeFormat(element.dataset.locale, {
		dateStyle: 'long',
		timeStyle: 'short'
	})

	const calendar = new Calendar(element, {
		plugins: [dayGridPlugin, listPlugin],
		locales: [frLocale],
		locale: element.dataset.locale,
		initialView: mobile.matches ? 'listMonth' : 'dayGridMonth',
		firstDay: 1,
		height: 'auto',
		fixedWeekCount: false,
		dayMaxEvents: 3,
		eventDisplay: 'block',
		displayEventEnd: true,
		noEventsText: element.dataset.empty,
		headerToolbar: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,listMonth'
		},
		events: JSON.parse(element.dataset.events),
		eventDidMount: ({ el, event }) => {
			el.tabIndex = 0
			el.setAttribute('role', 'button')
			el.setAttribute('aria-label', event.title)
			el.addEventListener('keydown', key => {
				if (key.key === 'Enter' || key.key === ' ') {
					key.preventDefault()
					openEvent(event)
				}
			})
		},
		eventClick: ({ event }) => openEvent(event)
	})

	function openEvent(event) {
		modal.querySelector('#event-title').textContent = event.title
		modal.querySelector('.event-date').textContent = dateFormat.format(event.start)
			+ (event.end ? ' – ' + dateFormat.format(event.end) : '')
		modal.querySelector('.event-description').replaceChildren(
			document.getElementById('event-description-' + event.id).content.cloneNode(true)
		)
		modal.showModal()
	}

	modal.querySelector('.event-close').addEventListener('click', () => modal.close())
	modal.addEventListener('click', event => {
		const bounds = modal.getBoundingClientRect()
		if (event.target === modal && (event.clientX < bounds.left || event.clientX > bounds.right
			|| event.clientY < bounds.top || event.clientY > bounds.bottom)) modal.close()
	})

	mobile.addEventListener('change', event => {
		calendar.changeView(event.matches ? 'listMonth' : 'dayGridMonth')
	})

	calendar.render()
})

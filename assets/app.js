import './bootstrap.js';
// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

import Navbar from "./js/navbar"
import Modal from "./js/modal"

import mediumZoom from 'medium-zoom'

document.addEventListener('DOMContentLoaded', () => {
  mediumZoom("[zoomable]")
  Navbar();
  Modal();
});


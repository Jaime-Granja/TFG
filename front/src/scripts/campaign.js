window.onload = function () {
let menu = document.getElementById("menuHamburguesa")
let fondo = document.getElementById("contenedor")
let fondo2 = document.getElementById("body")
let margen = document.getElementById("margin")
menu.addEventListener("click", abrirMenu)

function abrirMenu() {
    let botones = document.getElementById("menuHamburguesaBotones")
    botones.style.display = "flex"
    menu.style.display = "none"
    fondo.style.opacity = "50%"
    fondo2.style.opacity = "50%"
    margen.style.backgroundcolor = "red" 
}
}
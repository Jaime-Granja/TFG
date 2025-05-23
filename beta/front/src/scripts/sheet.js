window.onload = function () {
  let menu = document.getElementById("menuHamburguesa");
  let body = document.getElementById("body");
  let fondo = document.getElementById("contenedorPrincipal");
  let fondoSecundario = document.getElementById("contenedorSecundario");
  let margen = document.getElementById("margin");
  let botones = document.getElementById("menuHamburguesaBotones");
  let main = document.getElementById("mainPageBotton");
  let trasfondo = document.getElementById("backgroundBotton");
  let rasgos = document.getElementById("featuresBotton");
  let equipamientos = document.getElementById("equipmentBotton");
  let hechizos = document.getElementById("spellbookBotton");
  let trasfondoTexto = document.getElementById("backgroundPage");
  let rasgosTexto = document.getElementById("featuresPage");
  let equipamientoTexto = document.getElementById("equipmentPage");
  let hechizosTexto = document.getElementById("spellbookPage");

  main.addEventListener("click", mainPage);
  trasfondo.addEventListener("click", trasfondoMenu);
  rasgos.addEventListener("click", rasgosMenu);
  equipamientos.addEventListener("click", equipamientoMenu);
  hechizos.addEventListener("click", hechizosMenu);
  menu.addEventListener("click", abrirMenu);
  document.addEventListener("click", function (event) {
    cerrarMenu(event);
  });

  function abrirMenu() {
    botones.style.display = "flex";
    menu.style.display = "none";
    margen.style.height = window.getComputedStyle(body).height;
    fondo.style.opacity = "50%";
    fondoSecundario.style.opacity = "50%";
    margen.style.backgroundColor = "#242848";
    margen.style.opacity = "90%";
  }
  function cerrarMenu(event) {
    if (!margen.contains(event.target) && event.target !== menu) {
      botones.style.display = "none";
      menu.style.display = "block";
      fondo.style.opacity = "100%";
      fondoSecundario.style.opacity = "100%";
      margen.style.backgroundColor = "transparent";
    }
  }
  function mainPage() {
    //Fondos
    fondo.style.display = "block";
    fondoSecundario.style.display = "none";

    //Botones
    main.style.display = "none";
    trasfondo.style.display = "block";
    rasgos.style.display = "block";
    equipamientos.style.display = "block";
    hechizos.style.display = "block";

    //Textos
    trasfondoTexto.style.display = "none";
    rasgosTexto.style.display = "none";
    equipamientoTexto.style.display = "none";
    hechizosTexto.style.display = "none";
  }
  function trasfondoMenu() {
    //Fondos
    fondo.style.display = "none";
    fondoSecundario.style.display = "flex";

    //Botones
    main.style.display = "block";
    trasfondo.style.display = "none";
    rasgos.style.display = "block";
    equipamientos.style.display = "block";
    hechizos.style.display = "block";

    //Textos
    trasfondoTexto.style.display = "block";
    rasgosTexto.style.display = "none";
    equipamientoTexto.style.display = "none";
    hechizosTexto.style.display = "none";
  }
  function rasgosMenu() {
    //Fondos
    fondo.style.display = "none";
    fondoSecundario.style.display = "flex";

    //Botones
    main.style.display = "block";
    trasfondo.style.display = "block";
    rasgos.style.display = "none";
    equipamientos.style.display = "block";
    hechizos.style.display = "block";

    //Textos
    trasfondoTexto.style.display = "none";
    rasgosTexto.style.display = "block";
    equipamientoTexto.style.display = "none";
    hechizosTexto.style.display = "none";
  }
  function equipamientoMenu() {
    //Fondos
    fondo.style.display = "none";
    fondoSecundario.style.display = "flex";

    //Botones
    main.style.display = "block";
    trasfondo.style.display = "block";
    rasgos.style.display = "block";
    equipamientos.style.display = "none";
    hechizos.style.display = "block";

    //Textos
    trasfondoTexto.style.display = "none";
    rasgosTexto.style.display = "none";
    equipamientoTexto.style.display = "flex";
    hechizosTexto.style.display = "none";
  }

  function hechizosMenu() {
    //Fondos
    fondo.style.display = "none";
    fondoSecundario.style.display = "flex";

    //Botones
    main.style.display = "block";
    trasfondo.style.display = "block";
    rasgos.style.display = "block";
    equipamientos.style.display = "block";
    hechizos.style.display = "none";

    //Textos
    trasfondoTexto.style.display = "none";
    rasgosTexto.style.display = "none";
    equipamientoTexto.style.display = "none";
    hechizosTexto.style.display = "block"
  }
};

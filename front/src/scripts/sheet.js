window.onload = function () {
    let menu = document.getElementById("menuHamburguesa");
    let fondo = document.getElementById("contenedorPrincipal");
    let fondoSecundario = document.getElementById("contenedorSecundario");
    let margen = document.getElementById("margin");
    let botones = document.getElementById("menuHamburguesaBotones");
    let main = document.getElementById("mainPageBotton");
    let trasfondo = document.getElementById("backgroundBotton");
    let rasgos = document.getElementById("featuresBotton");

    let trasfondoTexto = document.getElementById("backgroundPage");
    let rasgosTexto = document.getElementById("featuresPage");


    main.addEventListener("click", mainPage);
    trasfondo.addEventListener("click", trasfondoMenu);
    rasgos.addEventListener("click", rasgosMenu);
    menu.addEventListener("click", abrirMenu);
    document.addEventListener("click", function (event) {
        cerrarMenu(event);
    });

    function abrirMenu() {
        botones.style.display = "flex";
        menu.style.display = "none";
        fondo.style.opacity = "50%";
        fondoSecundario.style.opacity = "50%";
        margen.style.height = window.getComputedStyle(fondo).height;
        margen.style.backgroundColor = "black";
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

        //Textos
        trasfondoTexto.style.display = "none";
        rasgosTexto.style.display = "none";
    }
    function trasfondoMenu() {
        //Fondos
        fondo.style.display = "none";
        fondoSecundario.style.display = "flex";
        
        //Botones
        main.style.display = "block";
        trasfondo.style.display = "none";
        rasgos.style.display = "block"

        //Textos
        trasfondoTexto.style.display = "block";
        rasgosTexto.style.display = "none";
    }
    function rasgosMenu() {
        //Fondos
        fondo.style.display = "none";
        fondoSecundario.style.display = "flex";

        //Botones
        main.style.display = "block";
        trasfondo.style.display = "block";
        rasgos.style.display = "none";
        
        //Textos
        trasfondoTexto.style.display = "none";
        rasgosTexto.style.display = "block";
    }

}
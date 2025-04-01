window.onload = function () {
    //Eventos de todos los botones.
    let LogIn = document.getElementById("LogIn")
    LogIn.addEventListener("click", log)
    let Register = document.getElementById("Register")
    Register.addEventListener("click", newUser)
    let Nosotros = document.getElementById("Us")
    Nosotros.addEventListener("click", nosotros)
    let GoInto = document.getElementById("goInto")
    GoInto.addEventListener("click", dentro)
    let GoBack = document.getElementById("goBack")
    GoBack.addEventListener("click", atras)
    let newGoInto = document.getElementById("newGoInto")
    newGoInto.addEventListener("click", dentro)
    let newGoBack = document.getElementById("newGoBack")
    newGoBack.addEventListener("click", atras)
    let nosotrosGoBack = document.getElementById("nosotrosGoBack")
    nosotrosGoBack.addEventListener("click", atras)
    //Acceso al Log In.
    function log() {
        let Texto = document.getElementById("Texto")
        let Log = document.getElementById("Log")
        let Botones = document.getElementById("botones")

        Texto.style.display = "none"
        Log.style.display = "block"
        Botones.style.display = "none"
    }
    //Acceso al Registro
    function newUser() {
        let Texto = document.getElementById("Texto")
        let New = document.getElementById("New")
        let Botones = document.getElementById("botones")

        Texto.style.display = "none"
        New.style.display = "block"
        Botones.style.display = "none"
    }
    //Acceso a la sección Nosotros
    function nosotros() {
        let Texto = document.getElementById("Texto")
        let Nosotros = document.getElementById("Nosotros")
        let Botones = document.getElementById("botones")
        Texto.style.display = "none"
        Nosotros.style.display = "block"
        Botones.style.display = "none"
    }
    //Avanzar a la siguiente página ya logueado.
    function dentro() {
        window.location.href = "home.html"
    }
    //Retroceder a la página de inicio.
    function atras() {
        let Texto = document.getElementById("Texto")
        let Log = document.getElementById("Log")
        let New = document.getElementById("New")
        let Nosotros = document.getElementById("Nosotros")
        let Botones = document.getElementById("botones")
        if (window.location.href = "home.html") {
            window.location.href = "index.html"
        }
        Texto.style.display = "block"
        Log.style.display = "none"
        New.style.display = "none"
        Nosotros.style.display = "none"
        Botones.style.display = "flex"
    }
}
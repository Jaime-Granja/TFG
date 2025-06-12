window.onload = function () {
let goBackButton = document.getElementById("goBackButton");
let logOut = document.getElementById("logOut");
goBackButton.addEventListener("click", goBack);
logOut.addEventListener("click", atras);
function goBack() {
    window.location.href = "../front/home.php";
  }
function atras() {
    window.location.href = "../back/logout.php";
  }
}
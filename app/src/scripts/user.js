window.onload = function () {
  let edit = document.getElementById("edit");
  edit.addEventListener("click", edicion);
  let editPassword = document.getElementById("editPassword");
  editPassword.addEventListener("click", editarContrasena);
  let goBackUsuario = document.getElementById("goBackUser");
  goBackUsuario.addEventListener("click", goBackUser);
  function edicion() {
    let oldInfo = document.getElementById("infoUsuario");
    let profilePic = document.getElementById("profilePic");
    let changeInfo = document.getElementById("changeInfo");
    oldInfo.style.display = "none";
    profilePic.style.display = "none";
    changeInfo.style.display = "block";
  }
  function editarContrasena() {
    let oldInfo = document.getElementById("infoUsuario");
    let profilePic = document.getElementById("profilePic");
    let changePassword = document.getElementById("changePassword");
    oldInfo.style.display = "none";
    profilePic.style.display = "none";
    changePassword.style.display = "block";
  }
  function goBackUser() {
    let oldInfo = document.getElementById("infoUsuario");
    if (
      changeInfo.style.display != "none" ||
      changePassword.style.display != "none"
    ) {
      oldInfo.style.display = "grid";
      profilePic.style.display = "block";
      changePassword.style.display = "none";
      changeInfo.style.display = "none";
    } else {
      window.location.href = "../front/home.php";
    }
  }
  const popup = document.getElementById("popup");
  if (popup && popup.textContent.trim() !== "") {
    popup.style.display = "block";
    popup.classList.add("show");
    setTimeout(() => {
      popup.classList.remove("show");
      setTimeout(() => (popup.style.display = "none"), 500);
    }, 4000);
  }
};

window.onload = function () {
   let logOut = document.getElementById("logOut");
  let join = document.getElementById("join");
  let create = document.getElementById("create");
  let goBackButton = document.getElementById("goBackButton")

  logOut.addEventListener("click", atras);
  join.addEventListener("click", joinCampaign);
  create.addEventListener("click", createCampaign);
  goBackButton.addEventListener("click", goBack)

  function joinCampaign() {
    let formJoin = document.getElementById("joinCampaign");
    let formCreate = document.getElementById("createCampaign");
    if (formJoin.style.display == "block") {
      formJoin.style.display = "none";
    } else {
      formJoin.style.display = "block";
    }

    formCreate.style.display = "none";
  }

  function createCampaign() {
    let formCreate = document.getElementById("createCampaign");
    let formJoin = document.getElementById("joinCampaign");
    if (formCreate.style.display == "block") {
      formCreate.style.display = "none";
    } else {
      formCreate.style.display = "block";
    }

    formJoin.style.display = "none";
  }
  function goBack() {
    window.location.href = "../front/home.php";
  }

    function atras() {
    window.location.href = "../back/logout.php";
  }
  
};

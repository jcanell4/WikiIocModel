function openNav() {
    console.log(document.getElementById("mySidenav"));

    document.getElementById("mySidenav").classList.remove("close");
    document.getElementById("mySidenav").classList.add("open");
    document.getElementById("myHamburger").classList.remove("visible");
    document.getElementById("myHamburger").classList.add("hidden");
    document.getElementById("myContentIndex").classList.remove("fade_out");
    document.getElementById("myContentIndex").classList.add("fade_in");
}

function closeNav() {
    document.getElementById("mySidenav").classList.remove("open");
    document.getElementById("mySidenav").classList.add("close");
    document.getElementById("myHamburger").classList.remove("hidden");
    document.getElementById("myHamburger").classList.add("visible");
    document.getElementById("myContentIndex").classList.remove("fade_in");
    document.getElementById("myContentIndex").classList.add("fade_out");
}

function printDoc() {
    window.open("btxfinals.pdf");
}

function switchopcl(bt){
    var childrenDiv = bt.parentNode.nextElementSibling;
    bt.classList.toggle("cl");
    bt.classList.toggle("op");
    childrenDiv.classList.toggle("hidden");
}

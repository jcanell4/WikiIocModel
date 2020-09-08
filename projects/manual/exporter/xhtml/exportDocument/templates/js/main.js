function openNav() {
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
    window.open("manual.pdf");
}

var menuExpanded = true;
function expandCollapse() {
    return;
    if (menuExpanded) {
        document.getElementById("expandCollapse").classList.remove("less");
        document.getElementById("expandCollapse").classList.add("more");
        
        if (document.getElementById("toc_div_id_level_2_1")) {
            document.getElementById("toc_div_id_level_2_1").classList.remove("more");
            document.getElementById("toc_div_id_level_2_1").classList.add("less");
        }
        if (document.getElementById("toc_div_id_level_2_2")) {
            document.getElementById("toc_div_id_level_2_2").classList.remove("more");
            document.getElementById("toc_div_id_level_2_2").classList.add("less");
        }
        if (document.getElementById("toc_div_id_level_2_3")) {
            document.getElementById("toc_div_id_level_2_3").classList.remove("more");
            document.getElementById("toc_div_id_level_2_3").classList.add("less");
        }
        if (document.getElementById("toc_div_id_level_2_4")) {
            document.getElementById("toc_div_id_level_2_4").classList.remove("more");
            document.getElementById("toc_div_id_level_2_4").classList.add("less");
        }
        if (document.getElementById("toc_div_id_level_2_5")) {
            document.getElementById("toc_div_id_level_2_5").classList.remove("more");
            document.getElementById("toc_div_id_level_2_5").classList.add("less");
        }
        if (document.getElementById("toc_div_id_level_2_6")) {
            document.getElementById("toc_div_id_level_2_6").classList.remove("more");
            document.getElementById("toc_div_id_level_2_6").classList.add("less");
        }
    }else {
        document.getElementById("expandCollapse").classList.remove("more");
        document.getElementById("expandCollapse").classList.add("less");
        
        if (document.getElementById("toc_div_id_level_2_1")) {
            document.getElementById("toc_div_id_level_2_1").classList.remove("less");
            document.getElementById("toc_div_id_level_2_1").classList.add("more");
        }
        if (document.getElementById("toc_div_id_level_2_2")) {
            document.getElementById("toc_div_id_level_2_2").classList.remove("less");
            document.getElementById("toc_div_id_level_2_2").classList.add("more");
        }
        if (document.getElementById("toc_div_id_level_2_3")) {
            document.getElementById("toc_div_id_level_2_3").classList.remove("less");
            document.getElementById("toc_div_id_level_2_3").classList.add("more");
        }
        if (document.getElementById("toc_div_id_level_2_4")) {
            document.getElementById("toc_div_id_level_2_4").classList.remove("less");
            document.getElementById("toc_div_id_level_2_4").classList.add("more");
        }
        if (document.getElementById("toc_div_id_level_2_5")) {
            document.getElementById("toc_div_id_level_2_5").classList.remove("less");
            document.getElementById("toc_div_id_level_2_5").classList.add("more");
        }
        if (document.getElementById("toc_div_id_level_2_6")) {
            document.getElementById("toc_div_id_level_2_6").classList.remove("less");
            document.getElementById("toc_div_id_level_2_6").classList.add("more");
        }
    }
    menuExpanded = !menuExpanded;
}

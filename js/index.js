window.addEventListener("load", _ => {
    const pfp = document.getElementById("pfp");
    pfp.addEventListener("mouseover", e => {
        e.target.src = e.target.dataset.nsfw;
    });
    pfp.addEventListener("mouseout", e => {
        e.target.src = e.target.dataset.sfw;
    });
});
// Fenêtre explicative d'une cause : ouverte AVANT de cocher la case.
document.addEventListener('DOMContentLoaded', function () {
    var modale = document.getElementById('fenetre-cause');
    if (!modale) return;
    var titre = document.getElementById('cause-titre');
    var texte = document.getElementById('cause-texte');
    var video = document.getElementById('cause-video');
    var check = document.getElementById('cause-checkbox');
    var cible = null;

    document.querySelectorAll('.btn-explication').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            cible = document.getElementById(btn.dataset.cible);
            if (!cible) return;
            check.checked = cible.checked;
            titre.textContent = btn.dataset.titre || '';
            texte.textContent = btn.dataset.explication || '';
            if (btn.dataset.video) {
                video.src = btn.dataset.video;
                video.hidden = false;
            } else {
                video.removeAttribute('src');
                video.hidden = true;
            }
            modale.hidden = false;
        });
    });

    check.addEventListener('change', function () {
        if (cible) cible.checked = check.checked;
    });

    document.getElementById('fermer-cause').addEventListener('click', function () {
        modale.hidden = true;
    });
    modale.addEventListener('click', function (e) {
        if (e.target === modale) modale.hidden = true;
    });
});

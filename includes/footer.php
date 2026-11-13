</main>
<footer class="site-footer">
  <p>Plastoyo — Aide aux opérateurs d'injection : défauts du process, causes et remèdes.
  Défauthèque générale gratuite <?= DUREE_ESSAI_MOIS ?> mois, puis <?= ABONNEMENT_PRIX ?>.</p>
</footer>
<div id="fenetre-cause" class="modale" hidden>
  <div class="modale-contenu">
    <button type="button" class="modale-fermer" id="fermer-cause">×</button>
    <h3 id="cause-titre"></h3>
    <p id="cause-texte"></p>
    <video id="cause-video" controls hidden></video>
    <label class="modale-coche"><input type="checkbox" id="cause-checkbox"> Je confirme avoir lu l'explication et je coche cette cause</label>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/modale.js"></script>
</body>
</html>

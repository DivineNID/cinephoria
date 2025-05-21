// src/Views/home/index.php
<div class="hero-section bg-dark text-white py-5 mb-4">
    <div class="container">
        <h1>Bienvenue sur <?= SITE_NAME ?></h1>
        <p class="lead">Découvrez le meilleur du cinéma dans nos salles</p>
        <p>20% de notre chiffre d'affaires est reversé à des initiatives écologiques</p>
        <a href="/reservation" class="btn btn-primary btn-lg">Réserver maintenant</a>
    </div>
</div>

<div class="container">
    <section class="mb-5">
        <h2>Dernières sorties</h2>
        <div class="row">
            <?php foreach ($latestFilms as $film): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= htmlspecialchars($film->getPosterUrl()) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($film->getTitle()) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($film->getTitle()) ?></h5>
                            <?php if ($film->isFavorite()): ?>
                                <span class="badge bg-warning">Coup de cœur</span>
                            <?php endif; ?>
                            <p class="card-text"><?= htmlspecialchars($film->getShortDescription()) ?></p>
                            <a href="/films/<?= $film->getId() ?>" class="btn btn-primary">Plus d'infos</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <section class="mb-5">
        <h2>Nos cinémas</h2>
        <div class="row">
            <?php foreach ($cinemas as $cinema): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($cinema->getName()) ?></h5>
                            <p class="card-text">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?= htmlspecialchars($cinema->getAddress()) ?>
                            </p>
                            <p class="card-text">
                                <i class="fas fa-phone"></i> 
                                <?= htmlspecialchars($cinema->getPhone()) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
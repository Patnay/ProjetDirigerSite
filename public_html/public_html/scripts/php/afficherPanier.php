           <section class="products-grid"
                style="display: flex; 
                       flex-direction: column; 
                       overflow: scroll; 
                       height: min-content;
                       width: min-content;">
                
                <?php foreach ($produits as $produit): ?>

                    <div class="product-card" id="card-<?= $produit['idItem'] ?>">

                        <div class="product-image">
                            <img src="images/<?= htmlspecialchars($produit['photo']) ?>" alt="">
                        </div>

                        <h3><?= htmlspecialchars($produit['nom']) ?></h3>

                        <p class="price" 
                           id="prix-<?= $produit['idItem'] ?>" 
                           data-prix="<?= $produit['prix'] ?>">
                            <?= number_format($produit['prix'] * $produit['quantitePanier'], 2) ?>
                            (<?= number_format($produit['prix'], 2) ?>/u)
                        </p>

                        <p class="number">
                            <button onclick="modifierQuantite(<?= $produit['idItem'] ?>, 'plus')">+</button>
                            <span id="qte-<?= $produit['idItem'] ?>">
                                <?= $produit['quantitePanier'] ?>
                            </span>
                            <button onclick="modifierQuantite(<?= $produit['idItem'] ?>, 'moins')">-</button>
                        </p>

                    </div>

                <?php endforeach; ?>

            </section>
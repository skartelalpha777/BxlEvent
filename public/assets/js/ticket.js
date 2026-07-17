
function updateQty(idTicket, variation) {
    const champInput = document.getElementById('qty-' + idTicket);
    let nouvelleQuantite = parseInt(champInput.value) + variation;

    // On vérifie que la quantité soit pas superieur a 10 et pas inferieur a 0
    if (nouvelleQuantite >= 0 && nouvelleQuantite <= 10) {
        champInput.value = nouvelleQuantite;
        calculerPanier(); // On met à jour le visuel à droite
    }
}

// Fonction qui recalcule le résumé de la commande
function calculerPanier() {
    let totalTickets = 0;
    let sousTotal = 0;

    document.querySelectorAll('.ticket-qty').forEach(function (input) {
        let quantite = parseInt(input.value);

        if (quantite > 0) {
            totalTickets += quantite;

            let prixUnitaire = parseFloat(input.getAttribute('data-price'));
            sousTotal += (quantite * prixUnitaire);

        }
    });

    // Mise à jour de l'affichage
    if (totalTickets > 0) {
        document.getElementById('summary-text').innerText = totalTickets + " ticket(s)";
        document.getElementById('summary-subtotal').innerText = sousTotal + " €";
        document.getElementById('summary-total').innerText = sousTotal  + " €";

        document.getElementById('btn-submit').disabled = false;



    } else {
        document.getElementById('summary-text').innerText = "Aucun ticket sélectionné";
        document.getElementById('summary-subtotal').innerText = "0 €";
        document.getElementById('summary-total').innerText = "0 €";
        document.getElementById('btn-submit').disabled = true;



    }
}
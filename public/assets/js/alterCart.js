// permet de d'envoyer directement  formulaire au contolleur quand on modifie la quantité 
//d'un article dans le panier. Afin que la quantité soit mise à jour
const form = document.querySelectorAll('.quantity');
form.forEach(input => {
    input.onchange = function () {
        this.form.submit();
    }
})

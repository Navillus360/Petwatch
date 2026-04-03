/**
 * This will allow the cards in the my_pets page to smoothly change from a normal card to one that can allow users
 * To report their pet missing without having to navigate to another page
 */

/**
 * Shows the normal card on load
 */
window.onload = function () {
    reportMissing(false);
};

function reportMissing(showReportCard){
    const normalCard = document.getElementById("normalCard")
    const reportMissingCard = document.getElementById("reportMissingCard")
    if(showReportCard){
        normalCard.style.display = 'none';
        reportMissingCard.style.display = 'block'
    }else{
        normalCard.style.display = 'block';
        reportMissingCard.style.display = 'none'
    }
}
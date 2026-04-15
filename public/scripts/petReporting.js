/**
 * This will allow the cards in the my_pets page to smoothly change from a normal card to one that can allow users
 * To report their pet missing without having to navigate to another page
 */

/**
 * Shows the normal card on load
 */
window.onload = function () {
    document.querySelectorAll('.card-body').forEach(card => {
        card.querySelector('.normalCard').style.display = 'block';
        card.querySelector('.reportMissingCard').style.display = 'none';
    });
};

function reportMissing(button, showReportCard) {
    const cardBody = button.closest('.card-body');
    const normalCard = cardBody.querySelector('.normalCard');
    const reportMissingCard = cardBody.querySelector('.reportMissingCard');
    if (showReportCard) {
        normalCard.style.display = 'none';
        reportMissingCard.style.display = 'block';
    } else {
        normalCard.style.display = 'block';
        reportMissingCard.style.display = 'none';
    }
}
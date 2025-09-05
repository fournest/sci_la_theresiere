// document.addEventListener('DOMContentLoaded', () => {
//         const modalOverlays = document.querySelectorAll('.modal-overlay');

//     modalOverlays.forEach(modalOverlay => {
//                 modalOverlay.style.display = 'flex'; 
        
//         requestAnimationFrame(() => {
//             modalOverlay.classList.add('show');
//         });

        
//         const closeButton = modalOverlay.querySelector('.modal-close-button');

        
//         const closeModal = () => {
//             modalOverlay.classList.remove('show');
            
//             modalOverlay.addEventListener('transitionend', () => {
//                 if (!modalOverlay.classList.contains('show')) {
//                     modalOverlay.style.display = 'none';
//                 }
//             }, { once: true }); 
//         };

        
//         if (closeButton) {
//             closeButton.addEventListener('click', closeModal);
//         }

       
//         modalOverlay.addEventListener('click', (event) => {
            
//             if (event.target === modalOverlay) {
//                 closeModal();
//             }
//         });
//     });
// });
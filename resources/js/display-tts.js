window.playTicketAnnouncement = function (ticketCode, moduleNumber) {
    if (!('speechSynthesis' in window)) {
        return;
    }

    const synth = window.speechSynthesis;

    synth.cancel();

    const text = `Turno ${ticketCode}, acérquese al Módulo ${moduleNumber}`;

    let repetitions = 0;
    const maxRepetitions = 3;

    function speak() {
        if (repetitions >= maxRepetitions) {
            return;
        }

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'es-ES';
        utterance.rate = 0.9;
        utterance.pitch = 1.0;

        utterance.onend = function () {
            repetitions++;
            setTimeout(speak, 400);
        };

        synth.speak(utterance);
    }

    speak();
};


define([
    'jquery',
], function($) {

    /**
     * Configuration function
     *
     * @param {Object} config
     * @param {*} element
     */
    return function (config, element) {

        var searchInput = $(element);
        if(searchInput){
            var buttonStart = $("<button class='action search search-button-voice ready' type='button'></button>");
            searchInput.closest('form').find('.actions').append(buttonStart);

            var buttonStop = $("<button class='action search search-button-voice stop' type='button'></button>");
            buttonStop.hide();
            searchInput.closest('form').find('.actions').append(buttonStop);

            buttonStart.click(function () {
                Tooso.speech.start({
                    onStart: function () {
                        console.log('>>> onStart');
                        buttonStart.hide();
                        buttonStop.show();
                        searchInput.val("");
                        buttonStart.removeClass('in-error');
                    },
                    onText: function (e) {
                        console.log('>>> onText', e.text);
                        searchInput.val(searchInput.val()+e.text);
                    },
                    onError: function (error) {
                        console.error('>>> onError', error);
                        buttonStop.hide();
                        buttonStart.show();
                        buttonStart.addClass('in-error');
                    },
                    onEnd: function () {
                        console.log('>>> onEnd');
                        buttonStop.hide();
                        buttonStart.show();
                        if (searchInput.val().trim() !== "" && !buttonStart.hasClass('in-error')){
                            searchInput.closest('form').submit();
                        }
                    },
                })
            });

            buttonStop.click(function () {
                Tooso.speech.stop()
            })
        }else{
            console.error("Tooso: SpeechToText search input not found");
        }
    }
});

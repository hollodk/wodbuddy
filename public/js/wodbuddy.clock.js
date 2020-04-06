var type = null;

var startPause = 5;

// clock
var clockCounter = 0;

// timer
var countDown = 60*20;

// emom
var emomSeconds = 60;
var emomRound = 0;
var emomCountDown = null;

// tabata
var tabataInterval = 20;
var tabataCountDown = 0;
var tabataPause = 0;
var tabataRound = 0;

var timerInterval = null;

$(document).ready(function() {
    $('#wodbuddy-reset').hide();
    $('#wodbuddy-pause').hide();
    $('#wodbuddy-start').hide();
    $('#wodbuddy-finish').hide();

    updateParticipants();

    setInterval(function() {
        updateParticipants();
    }, 5000);

    if (pingUrl) {
        setInterval(function() {
            $.get(pingUrl);
        }, 15000);
    }
});

function updateParticipants()
{
    $.get(participantsUrl, function(data) {
        $('#participant-box').html('');

        $.each(data.participants, function(key, value) {
            if (value.is_me) {
                $('#participant-box').append('<tr><td><b>'+value.name+'</b></td><td class="text-right">'+value.time_ago+'</td></tr>');
            } else {
                $('#participant-box').append('<tr><td>'+value.name+'</td><td class="text-right">'+value.time_ago+'</td></tr>');
            }
        });
    });
}

$('.wodbuddy-timer-type').on('click', function() {
    $('.wodbuddy-timer-type').hide();
    type = $(this).data('type');

    $('#wodbuddy-clock').html('Starting in '+startPause+' seconds');

    var x = setInterval(function() {
        startPause--;
        $('#wodbuddy-clock').html('Starting in '+startPause+' seconds');

        if (startPause <= 3) {
            $('#wodbuddy-box').addClass('bg-danger');
        }

        if (startPause <= 1) {
            clearInterval(x);
            startClock();
        }
    }, 1000);
});

function startClock()
{
    ding();

    switch (type) {
        case 'clock':
            timerInterval = setInterval(function() {
                defaultStart();

                clockCounter++;

                if ((clockCounter%60) == 0) ding();
                $('#wodbuddy-finish').show();

            }, 1000);

            break;

        case 'timer':
            timerInterval = setInterval(function() {
                defaultStart();

                countDown--;
                if ((countDown%60) == 0) ding();

                if (countDown <= 3) {
                    $('#wodbuddy-box').addClass('bg-danger');
                }

                if (countDown <= 0) {
                    finish();
                }

            }, 1000);

            break;

        case 'emom':
            timerInterval = setInterval(function() {
                if (emomRound == 0) {
                    emomCountDown = emomSeconds;
                    emomRound = 1;
                }
                defaultStart();

                emomCountDown--;

                if (emomCountDown <= 3) {
                    $('#wodbuddy-box').addClass('bg-danger');
                }

                if (emomCountDown <= 0 && emomRound <= 3) {
                    emomCountDown = emomSeconds;
                    emomRound++;

                    ding();

                } else if (emomCountDown <= 0) {
                    finish();
                }

            }, 1000);

            break;

        case 'tabata':
            timerInterval = setInterval(function() {

                if (tabataRound == 0) {
                    tabataCountDown = tabataInterval;
                    tabataRound++;
                }

                displayClock();
                $('#wodbuddy-box').removeClass('bg-danger');
                $('#wodbuddy-box').removeClass('bg-success');
                $('#wodbuddy-pause').show();

                if (tabataCountDown <= 3) {
                    $('#wodbuddy-box').addClass('bg-danger');
                }

                if (tabataCountDown <= 0 && tabataRound < 4) {
                    tabataPause = 10;
                    tabataCountDown = tabataInterval;
                    tabataRound++;

                    ding();

                } else if (tabataCountDown <= 0) {
                    finish();
                }

                if (tabataPause > 0) {
                    tabataPause--;

                    if (tabataPause == 0) ding();

                } else {
                    tabataCountDown--;
                }

            }, 1000);

            break;

        default:
            $('#wodbuddy-clock').html('not supported');

            break;
    }
}

function ding()
{
    var audio = new Audio('http://wodbuddy.fly-mailers.space/audio/ding.mp3');
    audio.play();
}

function defaultStart()
{
    displayClock();

    $('#wodbuddy-box').removeClass('bg-danger');
    $('#wodbuddy-pause').show();
}

function finish()
{
    clearInterval(timerInterval);

    $('#wodbuddy-box').removeClass('bg-danger');
    $('#wodbuddy-box').addClass('bg-success');

    $('#wodbuddy-pause').hide();
    $('#wodbuddy-reset').hide();
    $('#wodbuddy-start').hide();
    $('#wodbuddy-finish').hide();

    ding();
}

function displayClock()
{
    var seconds = null;
    var header = null;

    switch (type) {
        case 'clock':
            seconds = clockCounter;
            break;

        case 'timer':
            seconds = countDown;
            break;

        case 'emom':
            header = 'Round: '+emomRound+'<br>';
            seconds = emomCountDown;

            break;

        case 'tabata':
            if (tabataPause > 0) {
                header = 'Pause<br>';
                seconds = tabataPause;
            } else {
                header = 'Round: '+tabataRound+'<br>';
                seconds = tabataCountDown;
            }

            break;
    }

    var text = moment()
        .startOf('day')
        .seconds(seconds)
        .format('HH:mm:ss');

    if (header) {
        text = header+text;
    }

    $('#wodbuddy-clock').html(text);
}

$('#wodbuddy-pause').on('click', function() {
    $('#wodbuddy-pause').hide();
    $('#wodbuddy-reset').show();
    $('#wodbuddy-start').show();

    clearInterval(timerInterval);
});

$('#wodbuddy-reset').on('click', function() {
    $('#wodbuddy-pause').hide();
    $('#wodbuddy-reset').hide();
    $('#wodbuddy-start').show();

    clockCounter = 0;

    displayClock()
});

$('#wodbuddy-start').on('click', function() {
    $('#wodbuddy-reset').hide();
    $('#wodbuddy-start').hide();

    startClock();
});

$('#wodbuddy-finish').on('click', function() {
    finish();
});

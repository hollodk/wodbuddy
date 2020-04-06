var startDelay = wbConfig.delay;

// repeat counter
var repeatCounter = 0;

// clock
var clockCounter = 0;

// timer
var countStart = toSeconds(wbConfig.time);
var countDown = countStart;

// emom
var emomSeconds = toSeconds(wbConfig.emomtime);
var emomRound = 0;
var emomCountDown = null;

// tabata
var tabataInterval = 20;
var tabataPause = 10;
var tabataCountDown = 0;
var tabataRound = 0;

var timerInterval = null;
var readyInterval = null;
var monitorStarted = false;

// modal-form
$(document).ready(function() {
    updateModal();
});

$('#modal-form-type').on('change', function() {
    updateModal();
});

// wodbuddy stuff
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

// wodbuddy stuff
$('#wodbuddy-begin').on('click', function() {
    initStart();
});

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

function startMonitor()
{
    if (monitorStarted == false) {
        console.log('eeh');
        monitorStarted = true;

        readyInterval = setInterval(function() {
            $.get(readyUrl, function(data) {
                if (data.start == true) {
                    clearInterval(readyInterval);
                    initStart();
                }
            });
        }, 1000);
    }
}

function initStart()
{
    $('#wodbuddy-begin').hide();

    $('#wodbuddy-clock').html('Starting in '+startDelay+' seconds');

    var x = setInterval(function() {
        startDelay--;
        $('#wodbuddy-clock').html('Starting in '+startDelay+' seconds');

        if (startDelay <= 3) {
            $('#wodbuddy-box').addClass('bg-danger');
        }

        if (startDelay <= 0) {
            clearInterval(x);
            startClock();
        }
    }, 1000);
}

function updateModal()
{
    var value = $('#modal-form-type option:checked').val();

    switch (value) {
        case 'clock':
            $('#modal-time').hide();
            $('#modal-emomtime').hide();
            $('#modal-round').hide();
            $('#modal-repeat').hide();
            $('#modal-break').hide();

            break;

        case 'timer':
            $('#modal-time').show();
            $('#modal-emomtime').hide();
            $('#modal-round').hide();
            $('#modal-repeat').show();
            $('#modal-break').show();

            break;

        case 'tabata':
            $('#modal-time').hide();
            $('#modal-emomtime').hide();
            $('#modal-round').hide();
            $('#modal-repeat').show();
            $('#modal-break').show();

            break;

        case 'emom':
            $('#modal-time').hide();
            $('#modal-emomtime').show();
            $('#modal-round').show();
            $('#modal-repeat').show();
            $('#modal-break').show();

            break;
    }
}

function updateParticipants()
{
    $.get(participantsUrl, function(data) {
        $('#participant-box').html('');

        if (data.participants.length > 1) {
            startMonitor();
        }

        $.each(data.participants, function(key, value) {
            if (value.is_me) {
                $('#participant-box').append('<tr><td><b>&#62; '+value.name+'</b></td><td class="text-right">'+value.time_ago+'</td></tr>');
            } else {
                $('#participant-box').append('<tr><td>'+value.name+'</td><td class="text-right">'+value.time_ago+'</td></tr>');
            }
        });
    });
}

function startClock()
{
    ding();

    switch (wbConfig.type) {
        case 'clock':
            timerInterval = setInterval(function() {
                clockCounter++;
                defaultStart();

                if ((clockCounter%60) == 0) ding();
                $('#wodbuddy-finish').show();

            }, 1000);

            break;

        case 'timer':
            timerInterval = setInterval(function() {
                countDown--;
                defaultStart();

                if ((countDown%60) == 0) ding();

                if (countDown <= 3) {
                    $('#wodbuddy-box').addClass('bg-danger');
                }

                if (countDown <= 0) {
                    repeatCounter++;

                    if (repeatCounter < wbConfig.repeat) {
                        countDown = countStart;
                        startPause(toSeconds(wbConfig.break));

                    } else {
                        finish();
                    }
                }

            }, 1000);

            break;

        case 'emom':
            timerInterval = setInterval(function() {
                if (emomRound == 0) {
                    emomCountDown = emomSeconds;
                    emomRound = 1;
                }

                emomCountDown--;
                defaultStart();

                if (emomCountDown <= 3) {
                    $('#wodbuddy-box').addClass('bg-danger');
                }

                if (emomCountDown <= 0 && emomRound < wbConfig.round) {
                    emomCountDown = emomSeconds;
                    emomRound++;

                    ding();

                } else if (emomCountDown <= 0) {
                    repeatCounter++;

                    if (repeatCounter < wbConfig.repeat) {
                        emomRound = 0;
                        startPause(toSeconds(wbConfig.break));

                    } else {
                        finish();
                    }
                }

            }, 1000);

            break;

        case 'tabata':
            timerInterval = setInterval(function() {

                if (tabataRound == 0) {
                    tabataCountDown = tabataInterval;
                    tabataRound++;
                }

                tabataCountDown--;
                displayClock();

                $('#wodbuddy-box').removeClass('bg-danger');
                $('#wodbuddy-box').removeClass('bg-success');
                $('#wodbuddy-pause').show();

                if (tabataCountDown <= 3) {
                    $('#wodbuddy-box').addClass('bg-danger');
                }

                if (tabataCountDown <= 0 && tabataRound < 8) {
                    tabataCountDown = tabataInterval;
                    tabataRound++;

                    ding();

                    startPause(tabataPause);

                } else if (tabataCountDown <= 0) {
                    repeatCounter++;

                    if (repeatCounter < wbConfig.repeat) {
                        tabataRound = 0;
                        startPause(toSeconds(wbConfig.break));

                    } else {
                        finish();
                    }
                }

            }, 1000);

            break;
    }
}

function startPause(seconds)
{
    clearInterval(timerInterval);

    start = seconds;

    var x = setInterval(function() {
        start--;

        var text = moment()
            .startOf('day')
            .seconds(start)
            .format('HH:mm:ss');

        $('#wodbuddy-clock').html('Pause<br>'+text);

        if (start <= 0) {
            clearInterval(x);
            startClock();
        }

    }, 1000);
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

    switch (wbConfig.type) {
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
            header = 'Round: '+tabataRound+'<br>';
            seconds = tabataCountDown;

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

function toSeconds(input)
{
    if (input === undefined) return 0;

    var o = input.split(':');

    m = parseInt(o[0]);
    s = parseInt(o[1]);

    seconds = 0;
    seconds = seconds+m*60;
    seconds = seconds+s;

    return seconds;
}

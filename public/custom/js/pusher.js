"use strict";
$(document).ready(function() {

    // إشعارات الطلبات تخص أصحاب المطاعم والموظفين فقط —
    // لا صوت ولا اشتراك قنوات للعملاء والزوار
    var isClient = (typeof USER_IS_CLIENT !== 'undefined') && USER_IS_CLIENT;
    if (!PUSHER_APP_KEY || !USER_ID || isClient) {
        return;
    }

    var SOUND_URL = '/custom/sound/new-order.mp3';
    var audio = new Audio(SOUND_URL);
    audio.preload = 'auto';
    var audioUnlocked = false;

    // فك قفل الصوت عند أول لمسة — متطلب إجباري في iOS/Safari
    function unlockAudio() {
        if (audioUnlocked) return;
        audio.muted = true;
        audio.volume = 0;
        var p = audio.play();
        if (p !== undefined) {
            p.then(function() {
                audio.pause();
                audio.currentTime = 0;
                audio.muted = false;
                audio.volume = 1;
                audioUnlocked = true;
            }).catch(function() {
                audio.muted = false;
                audio.volume = 1;
            });
        }
    }
    document.addEventListener('touchstart', unlockAudio, { passive: true });
    document.addEventListener('click', unlockAudio);

    function playSound() {
        audio.currentTime = 0;
        var p = audio.play();
        if (p !== undefined) {
            p.catch(function(err) {
                console.warn('Sound blocked:', err);
            });
        }
    }

    var pusher = new Pusher(PUSHER_APP_KEY, {
        cluster: PUSHER_APP_CLUSTER
    });

    // عند رجوع الاتصال بعد انقطاع: حدّث الصفحة لجلب الطلبات الفائتة
    var wasDisconnected = false;
    pusher.connection.bind('state_change', function(states) {
        if (states.current === 'disconnected' || states.current === 'unavailable') {
            wasDisconnected = true;
        }
        if (states.current === 'connected' && wasDisconnected) {
            wasDisconnected = false;
            location.reload();
        }
    });
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && pusher.connection.state !== 'connected') {
            pusher.connect();
        }
    });

    var channel = pusher.subscribe('user.' + USER_ID);
    channel.bind('callwaiter-event', function(data) {
        js.notify(data.msg + " " + data.table.restoarea.name + " " + data.table.name, "primary");
        playSound();
    });

    channel.bind('neworder-event', function(data) {
        js.notify(data.msg + " #" + data.order.id, "primary");
        playSound();
    });
});
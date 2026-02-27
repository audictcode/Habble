(function () {
  var RADIO_STATE_KEY = 'habble.radio.state';
  var RADIO_BROADCAST_KEY = 'habble.radio.broadcast';
  var RADIO_CHANNEL_NAME = 'habble-radio-sync';
  var radioChannel = ('BroadcastChannel' in window) ? new BroadcastChannel(RADIO_CHANNEL_NAME) : null;

  function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
  }

  function normalizeState(raw) {
    var volume = Number(raw && raw.volume);
    var normalizedVolume = Number.isFinite(volume) ? clamp(Math.round(volume), 0, 100) : 65;
    return {
      isPlaying: Boolean(raw && raw.isPlaying),
      muted: Boolean(raw && raw.muted),
      volume: normalizedVolume
    };
  }

  function loadState() {
    try {
      var parsed = JSON.parse(localStorage.getItem(RADIO_STATE_KEY) || '{}');
      return normalizeState(parsed);
    } catch (error) {
      return normalizeState({});
    }
  }

  function saveState(state) {
    var normalized = normalizeState(state);
    localStorage.setItem(RADIO_STATE_KEY, JSON.stringify(normalized));
    return normalized;
  }

  function broadcastState(state, sourceId) {
    var payload = {
      type: 'radio-state',
      state: normalizeState(state),
      sourceId: sourceId,
      at: Date.now()
    };

    if (radioChannel) {
      radioChannel.postMessage(payload);
    }

    try {
      localStorage.setItem(RADIO_BROADCAST_KEY, JSON.stringify(payload));
    } catch (error) {
      // Storage might be unavailable in private modes; ignore sync fallback.
    }
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function buildHabboAvatarUrl(username, hotel) {
    var safeName = encodeURIComponent(username || 'Habble');
    var safeHotel = (hotel || 'es').toLowerCase();
    return 'https://www.habbo.' + safeHotel + '/habbo-imaging/avatarimage?user=' + safeName + '&direction=2&head_direction=3&size=l';
  }

  function parseDjResponse(payload) {
    if (!payload) {
      return { live: false, name: '', habboName: '', artist: '', title: '', track: '', listeners: 0 };
    }

    if (Array.isArray(payload) && payload.length > 0) {
      payload = payload[0];
    }

    if (typeof payload !== 'object') {
      return { live: false, name: '', habboName: '', artist: '', title: '', track: '', listeners: 0 };
    }

    var live = Boolean(
      payload.live ?? payload.is_live ?? payload.connected ?? payload.online ?? payload.on_air ?? false
    );

    var name = payload.name || payload.dj_name || payload.username || payload.habbo_name || '';
    var habboName = payload.habbo_name || payload.habbo || name || '';
    var trackFromNowPlaying = '';
    if (payload.now_playing && payload.now_playing.song && payload.now_playing.song.text) {
      trackFromNowPlaying = payload.now_playing.song.text;
    }
    var artist = payload.artist || payload.track_artist || (payload.now_playing && payload.now_playing.song ? payload.now_playing.song.artist : '') || '';
    var title = payload.title || payload.track_title || (payload.now_playing && payload.now_playing.song ? payload.now_playing.song.title : '') || '';
    var track = payload.track || payload.song || payload.title || trackFromNowPlaying || '';
    var listenersRaw = payload.listeners
      ?? payload.listener_count
      ?? payload.current_listeners
      ?? payload.online_listeners
      ?? (payload.now_playing && payload.now_playing.listeners ? payload.now_playing.listeners.current : 0)
      ?? 0;
    var listeners = Number.parseInt(listenersRaw, 10);

    return {
      live: live,
      name: String(name || '').trim(),
      habboName: String(habboName || '').trim(),
      artist: String(artist || '').trim(),
      title: String(title || '').trim(),
      track: String(track || '').trim(),
      listeners: Number.isNaN(listeners) ? 0 : Math.max(listeners, 0)
    };
  }

  function mountWidget(widget) {
    if (widget.dataset.radioMounted === '1') {
      return;
    }
    widget.dataset.radioMounted = '1';

    var playBtn = widget.querySelector('.header-radio-play');
    var stopBtn = widget.querySelector('.header-radio-stop');
    var volumeBtn = widget.querySelector('.header-radio-volume');
    var volumeRange = widget.querySelector('.header-radio-volume-range');
    var audio = widget.querySelector('.header-radio-audio');
    var djLabelEl = widget.querySelector('.header-radio-dj-label');
    var djNameEl = widget.querySelector('.header-radio-dj-name');
    var djTrackTextEl = widget.querySelector('.header-radio-track-text');
    var djListenersEl = widget.querySelector('.header-radio-listeners');
    var djAvatarEl = widget.querySelector('.header-radio-dj-avatar');
    var djProfileLink = widget.querySelector('.header-radio-dj-link');

    if (!playBtn || !stopBtn || !volumeBtn || !volumeRange || !audio || !djNameEl || !djAvatarEl) {
      return;
    }

    var streamUrl = widget.getAttribute('data-stream-url') || '';
    var djEndpoint = widget.getAttribute('data-dj-endpoint') || '';
    var hotel = widget.getAttribute('data-hotel') || 'es';
    var loadingText = widget.getAttribute('data-loading-text') || 'Loading...';
    var sourceId = 'radio-' + Math.random().toString(36).slice(2);

    var fallbackDjName = 'Habble';
    var fallbackHabboUser = 'Habble';
    var currentState = loadState();

    function setDjCard(name, habboUser, isLive, artist, title, track, listeners) {
      var label = isLive ? name : fallbackDjName;
      var avatarUser = isLive ? (habboUser || name) : fallbackHabboUser;
      var safeArtist = artist ? artist : loadingText;
      var safeTitle = title ? title : loadingText;
      var safeListeners = Number.isFinite(listeners) ? Math.max(listeners, 0) : 0;

      if (isLive && safeArtist === '-' && safeTitle === '-' && track) {
        var parts = String(track).split(' - ');
        if (parts.length >= 2) {
          safeArtist = parts.shift() || '-';
          safeTitle = parts.join(' - ') || '-';
        } else {
          safeTitle = track;
        }
      }

      djNameEl.textContent = label;
      djNameEl.setAttribute('title', label);
      if (djLabelEl) {
        djLabelEl.textContent = isLive ? 'En Vivo' : 'Auto DJ';
        djLabelEl.classList.toggle('is-live', isLive);
      }
      if (djTrackTextEl) {
        var trackText = (safeArtist === loadingText && safeTitle === loadingText)
          ? loadingText
          : (safeArtist + ' - ' + safeTitle);
        djTrackTextEl.textContent = trackText;
        djTrackTextEl.setAttribute('title', trackText);
      }
      if (djListenersEl) {
        var listenersText = 'Live listeners: ' + safeListeners;
        djListenersEl.textContent = listenersText;
        djListenersEl.setAttribute('title', listenersText);
      }

      djAvatarEl.src = buildHabboAvatarUrl(avatarUser, hotel);
      djAvatarEl.alt = escapeHtml(label) + ' avatar';
      if (djProfileLink) {
        djProfileLink.href = 'https://www.habbo.' + hotel + '/profile/' + encodeURIComponent(avatarUser);
      }
    }

    async function refreshDjInfo() {
      if (!djEndpoint) {
        setDjCard('', '', false, loadingText, loadingText, '', 0);
        return;
      }

      try {
        var endpointUrl = new URL(djEndpoint, window.location.origin);
        endpointUrl.searchParams.set('_', Date.now().toString());

        var response = await fetch(endpointUrl.toString(), {
          headers: { Accept: 'application/json' },
          cache: 'no-cache',
          credentials: 'same-origin'
        });

        if (!response.ok) {
          setDjCard('', '', false, loadingText, loadingText, '', 0);
          return;
        }

        var data = await response.json();
        var parsed = parseDjResponse(data);

        if (parsed.live && parsed.name.length > 0) {
          var normalizedDjName = parsed.name.toLowerCase();
          var liveHabboUser = normalizedDjName === 'eiver'
            ? 'Eiver'
            : (parsed.habboName || parsed.name);

          setDjCard(parsed.name, liveHabboUser, true, parsed.artist, parsed.title, parsed.track, parsed.listeners);
          return;
        }

        setDjCard('', '', false, parsed.artist, parsed.title, parsed.track, parsed.listeners);
      } catch (error) {
        setDjCard('', '', false, loadingText, loadingText, '', 0);
      }
    }

    function setPlayingState(isPlaying) {
      playBtn.classList.toggle('is-active', isPlaying);
      stopBtn.classList.toggle('is-active', !isPlaying);
    }

    function applyVolumeState(state) {
      var safeState = normalizeState(state);
      volumeRange.value = String(safeState.volume);
      audio.volume = safeState.volume / 100;
      audio.muted = safeState.muted || safeState.volume <= 0;
      volumeBtn.classList.toggle('is-active', audio.muted);
      currentState = safeState;
    }

    function applyPlaybackState(state) {
      if (state.isPlaying) {
        if (!audio.src) {
          return;
        }
        audio.play().then(function () {
          setPlayingState(true);
        }).catch(function () {
          currentState.isPlaying = false;
          saveState(currentState);
          setPlayingState(false);
        });
        return;
      }

      audio.pause();
      audio.currentTime = 0;
      setPlayingState(false);
    }

    function persistAndSync(partialState) {
      currentState = saveState({
        isPlaying: partialState.isPlaying ?? currentState.isPlaying,
        muted: partialState.muted ?? currentState.muted,
        volume: partialState.volume ?? currentState.volume
      });
      broadcastState(currentState, sourceId);
    }

    function handleIncomingState(payload) {
      if (!payload || payload.type !== 'radio-state' || !payload.state) {
        return;
      }
      if (payload.sourceId && payload.sourceId === sourceId) {
        return;
      }

      currentState = saveState(payload.state);
      applyVolumeState(currentState);
      applyPlaybackState(currentState);
    }

    audio.src = streamUrl;
    applyVolumeState(currentState);

    playBtn.addEventListener('click', function () {
      if (!audio.src) {
        return;
      }

      audio.play().then(function () {
        persistAndSync({ isPlaying: true });
        setPlayingState(true);
      }).catch(function () {
        persistAndSync({ isPlaying: false });
        setPlayingState(false);
      });
    });

    stopBtn.addEventListener('click', function () {
      audio.pause();
      audio.currentTime = 0;
      persistAndSync({ isPlaying: false });
      setPlayingState(false);
    });

    volumeRange.addEventListener('input', function () {
      var volume = clamp(Number(volumeRange.value), 0, 100);
      var muted = volume <= 0;
      applyVolumeState({
        isPlaying: currentState.isPlaying,
        muted: muted,
        volume: volume
      });
      persistAndSync({ volume: volume, muted: muted });
    });

    volumeBtn.addEventListener('click', function () {
      audio.muted = !audio.muted;
      volumeBtn.classList.toggle('is-active', audio.muted);
      persistAndSync({ muted: audio.muted });
    });

    audio.addEventListener('playing', function () {
      setPlayingState(true);
    });

    audio.addEventListener('pause', function () {
      setPlayingState(false);
    });

    if (radioChannel) {
      radioChannel.addEventListener('message', function (event) {
        handleIncomingState(event.data);
      });
    }

    window.addEventListener('storage', function (event) {
      if (event.key !== RADIO_BROADCAST_KEY || !event.newValue) {
        return;
      }
      try {
        handleIncomingState(JSON.parse(event.newValue));
      } catch (error) {
        // Ignore invalid sync payload.
      }
    });

    if (currentState.isPlaying) {
      applyPlaybackState(currentState);
    } else {
      setPlayingState(false);
    }

    setDjCard('', '', false, loadingText, loadingText, '', 0);
    refreshDjInfo();
    window.setInterval(refreshDjInfo, 2000);
  }

  function initRadioWidgets() {
    var widgets = document.querySelectorAll('.header-radio-widget');
    widgets.forEach(mountWidget);
  }

  document.addEventListener('DOMContentLoaded', initRadioWidgets);
  document.addEventListener('turbolinks:load', initRadioWidgets);
})();

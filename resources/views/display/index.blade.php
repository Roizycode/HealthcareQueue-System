@php
    $theme = \App\Models\QueueSetting::get('display_theme', 'dark');
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $theme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Queue Display - Smart Healthcare</title>
    <link rel="icon" type="image/png" href="{{ asset('image/Iconlogo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --hc-primary: #0D6EFD;
            --hc-secondary: #20C997;
            --hc-emergency: #DC3545;
            --hc-warning: #FFC107;
            --hc-success: #198754;
            --hc-bg: #f0f4f8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        /* Light Theme Overrides */
        html[data-theme="light"] body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        html[data-theme="light"] .logo,
        html[data-theme="light"] .clock {
            color: #1e293b;
        }
        
        html[data-theme="light"] .date {
            color: rgba(30, 41, 59, 0.7);
        }
        
        /* Upcoming Section - Light Theme */
        html[data-theme="light"] .upcoming-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        html[data-theme="light"] .upcoming-section .section-title {
            color: #475569;
        }
        
        html[data-theme="light"] .upcoming-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        
        html[data-theme="light"] .upcoming-item .position {
            background: #e2e8f0;
            color: #64748b;
        }
        
        html[data-theme="light"] .upcoming-item .queue-number {
            color: #1e293b;
        }
        
        html[data-theme="light"] .upcoming-item .service-name {
            color: #64748b;
        }
        
        html[data-theme="light"] .priority-regular {
            background: #e2e8f0;
            color: #475569;
        }
        
        html[data-theme="light"] .announcement-banner {
            background: rgba(255, 255, 255, 0.95);
            border-top: 1px solid #e2e8f0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        }
        
        html[data-theme="light"] .announcement-icon {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        html[data-theme="light"] .announcement-text {
            color: #1e293b;
        }
        
        /* Hide scrollbar globally */
        * {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        *::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
        
        /* Hide scrollbar for Chrome, Safari and Opera */
        ::-webkit-scrollbar {
            display: none;
        }
        
        /* Back to Menu Link */
        .back-to-menu {
            pointer-events: auto;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: opacity 0.3s ease;
        }
        
        .back-to-menu:hover {
            opacity: 0.7;
            color: white;
        }
        
        html[data-theme="light"] .back-to-menu {
            color: #1e293b;
        }
        
        html[data-theme="light"] .back-to-menu:hover {
            opacity: 0.7;
            color: #1e293b;
        }
        
        /* Header removed, using absolute positioning for clock */
        
        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .logo i {
            color: var(--hc-primary);
            font-size: 1.75rem;
        }
        
        .clock {
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .date {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
        }
        
        /* Main Grid */
        .display-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            padding: 8rem 1.5rem 6rem 1.5rem; /* Bottom padding for fixed banner */
            min-height: 100vh;
        }
        
        /* Now Serving Section */
        .now-serving-section {
            background: linear-gradient(135deg, var(--hc-primary) 0%, #0dcaf0 100%);
            border-radius: 24px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden; /* No scrollbar at all */
        }
        
        .now-serving-section::-webkit-scrollbar {
            display: none;
            width: 0;
            background: transparent;
        }
        
        .now-serving-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(20deg);
            pointer-events: none;
        }
        
        .section-title {
            color: rgba(255,255,255,0.8);
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            font-size: 0.9rem;
        }
        
        .serving-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            flex: 1;
            position: relative;
        }
        
        .serving-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.5s ease;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .serving-card .service-name {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .serving-card .queue-number {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .serving-card .counter-name {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
        }
        
        .serving-card.empty {
            background: rgba(255,255,255,0.05);
            border: 2px dashed rgba(255,255,255,0.2);
        }
        
        .serving-card.empty .queue-number {
            color: rgba(255,255,255,0.3);
            font-size: 2rem;
        }
        
        .serving-card.emergency {
            background: linear-gradient(135deg, #DC3545 0%, #fd7e14 100%);
            animation: emergencyPulse 1s infinite;
        }
        
        @keyframes emergencyPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            50% { box-shadow: 0 0 30px 10px rgba(220, 53, 69, 0.3); }
        }
        
        /* Upcoming Section */
        .upcoming-section {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .upcoming-section .section-title {
            color: rgba(255,255,255,0.6);
        }
        
        .upcoming-list {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .upcoming-item {
            background: rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        

        
        .upcoming-item .position {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.5);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            margin-right: 1rem;
        }
        
        .upcoming-item .queue-info {
            flex: 1;
        }
        
        .upcoming-item .queue-number {
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        .upcoming-item .service-name {
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
        }
        
        .upcoming-item .priority-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .priority-emergency { background: var(--hc-emergency); color: white; }
        .priority-senior { background: #9c27b0; color: white; }
        .priority-pwd { background: #0dcaf0; color: white; }
        .priority-regular { background: rgba(255,255,255,0.2); color: white; }
        
        /* Announcement Banner */
        .announcement-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(15, 23, 42, 0.95); /* Dark background matching theme */
            backdrop-filter: blur(15px);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            overflow: hidden;
            z-index: 1000;
            border-top: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 -4px 20px rgba(0,0,0,0.3);
        }
        
        .announcement-icon {
            background: rgba(255,255,255,0.2);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .announcement-text {
            color: white;
            font-weight: 400;
            white-space: nowrap;
            display: inline-block;
            padding-left: 100%;
            animation: marquee 30s linear infinite;
        }
        
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        
        /* Mobile Responsive */
        @media (max-width: 1200px) {
            .display-grid {
                grid-template-columns: 1fr;
            }
            
            .serving-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .serving-card .queue-number {
                font-size: 2.5rem;
            }
            
            .serving-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar: Menu & Clock -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: flex-start; z-index: 50; pointer-events: none;">
        <!-- Back Button (Text Only) -->
        <a href="{{ url('/') }}" class="back-to-menu">
            Back to Menu
        </a>

        <!-- Clock -->
        <div class="text-end">
            <div class="clock" id="clock" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">--:--</div>
            <div class="date" id="date" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">Loading...</div>
        </div>
    </div>
    
    <!-- Main Display Grid -->
    <div class="display-grid">
        <!-- Now Serving Section -->
        <div class="now-serving-section">
            <div class="section-title">
                <i class="fas fa-bullhorn"></i>
                Now Serving
            </div>
            <div class="serving-cards" id="servingCards">
                <!-- Will be populated by JavaScript -->
                <div class="serving-card empty">
                    <div class="service-name">Loading...</div>
                    <div class="queue-number">---</div>
                    <div class="counter-name">Please wait</div>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Section -->
        <div class="upcoming-section">
            <div class="section-title">
                <i class="fas fa-clock"></i>
                Coming Up Next
            </div>
            <div class="upcoming-list" id="upcomingList">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Announcement Banner -->
    <div class="announcement-banner">
        <div class="announcement-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="announcement-text">
            Welcome to Smart Healthcare! Please listen for your queue number and proceed to the designated counter when called. Thank you for your patience. &nbsp; • &nbsp; Senior citizens and PWDs have priority lanes. &nbsp; • &nbsp; For emergencies, please proceed directly to the Emergency Counter.
        </div>
    </div>

    <script>
        // Update clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
            document.getElementById('date').textContent = now.toLocaleDateString('en-US', { 
                weekday: 'long', 
                month: 'long', 
                day: 'numeric', 
                year: 'numeric' 
            });
        }
        setInterval(updateClock, 1000);
        updateClock();
        
        // Sound Logic
        let audioCtx;

        let announcedIds = new Set();
        let isFirstLoad = true;

        function initAudio() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
        }

        function playChime() {
            initAudio();
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(660, audioCtx.currentTime); 
            oscillator.frequency.exponentialRampToValueAtTime(880, audioCtx.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 1.5);
            
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 1.5);
        }

        function announceQueue(number, counter) {
            // Ensure audio context is ready
            try {
                if (audioCtx && audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            } catch (e) {
                console.log("Audio autoplay blocked. User must enable sound.");
            }

            playChime();
            setTimeout(() => {
                // Split number for clearer speech (e.g. A-001 -> "A Zero Zero One")
                const spokenText = number.replace('-', ' '); 
                const msg = new SpeechSynthesisUtterance(`Queue number ${spokenText}, please proceed to ${counter}`);
                
                // Attempt to select a female voice
                const voices = window.speechSynthesis.getVoices();
                const femaleVoice = voices.find(v => 
                    v.name.includes('Zira') || 
                    v.name.includes('Samantha') || 
                    v.name.includes('Female') ||
                    (v.lang === 'en-US' && !v.name.includes('David') && !v.name.includes('Mark'))
                );

                if (femaleVoice) {
                    msg.voice = femaleVoice;
                }
                
                msg.rate = 0.9;
                msg.pitch = 1.1; // Slightly higher pitch
                window.speechSynthesis.speak(msg);
            }, 1200);
        }
        
        // Load queue data
        function loadDisplayData() {
            fetch('/display/data')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        checkAnnouncements(data.data);
                        renderServingCards(data.data);
                        renderUpcomingList(data.data);
                    }
                })
                .catch(e => console.error('Error loading data:', e));
        }

        function checkAnnouncements(services) {
            let hasNewAnnouncements = false;
            
            services.forEach(service => {
                if (service.active_queues && service.active_queues.length > 0) {
                    service.active_queues.forEach(q => {
                        // Announce if status is 'called' and we haven't announced this ID yet
                        if (q.status === 'called' && !announcedIds.has(q.id)) {
                            // Skip announcement on first load to prevent noise storm
                            if (!isFirstLoad) {
                                announceQueue(q.queue_number, q.counter || 'Counter');
                            }
                            announcedIds.add(q.id);
                        }
                        // Also track serving IDs so we don't re-announce if they switch to serving (logic handled by status check)
                        if (q.status === 'serving') {
                            announcedIds.add(q.id);
                        }
                    });
                }
            });
            
            isFirstLoad = false;
        }
        
        // Global state for serving list
        let servingQueueData = [];
        let servingPageIndex = 0;
        let servingCycleStarted = false;

        function renderServingCards(services) {
            let all = [];
            
            services.forEach(service => {
                if (service.active_queues && service.active_queues.length > 0) {
                    service.active_queues.forEach(q => {
                        all.push({ 
                            type: 'active',
                            ...q, 
                            service_color: service.color, 
                            service_name: service.name 
                        });
                    });
                } else {
                    all.push({ 
                        type: 'empty', 
                        service_color: service.color, 
                        service_name: service.name 
                    });
                }
            });
            
            servingQueueData = all;
            
            if (!servingCycleStarted) {
                renderServingView();
                setInterval(cycleServing, 5000);
                servingCycleStarted = true;
            }
            
            // Re-render immediately to show updates (preserving page index)
            renderServingView();
        }
        
        function cycleServing() {
            // Cycle through pages of serving cards (6 per page)
            // This only changes card content, not page scroll position
            if (servingQueueData.length === 0) return;
            
            const pageSize = 6;
            const maxPages = Math.ceil(servingQueueData.length / pageSize);
            
            if (maxPages <= 1) {
                servingPageIndex = 0;
            } else {
                servingPageIndex++;
                if (servingPageIndex >= maxPages) {
                    servingPageIndex = 0;
                }
            }
            renderServingView();
        }
        
        function renderServingView() {
            const container = document.getElementById('servingCards');
            
            if (servingQueueData.length === 0) {
                container.innerHTML = `
                    <div class="serving-card empty">
                        <div class="queue-number">---</div>
                        <div class="counter-name">No queues active</div>
                    </div>
                `;
                return;   
            }
            
            const pageSize = 6;
            // Ensure index is valid
            const maxPages = Math.ceil(servingQueueData.length / pageSize);
            if (servingPageIndex >= maxPages) servingPageIndex = 0;
            
            const start = servingPageIndex * pageSize;
            const slice = servingQueueData.slice(start, start + pageSize);
            
            let html = '';
            
            slice.forEach(item => {
                if (item.type === 'active') {
                    const isEmergency = item.priority === 'EMG';
                    const statusClass = item.status === 'called' ? 'border-primary' : '';
                    
                     html += `
                        <div class="serving-card ${isEmergency ? 'emergency' : ''} ${statusClass} animate-fadeIn" style="border-bottom: 4px solid ${item.service_color};">
                            <div class="service-name">
                                ${item.service_name} 
                                ${item.status === 'called' ? '<span class="badge bg-warning text-dark ms-1 animate-pulse">CALLING</span>' : ''}
                            </div>
                            <div class="queue-number">${item.queue_number}</div>
                            <div class="counter-name">
                                <i class="fas fa-desktop"></i> ${item.counter || 'Counter'}
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="serving-card empty animate-fadeIn" style="border-bottom: 4px solid ${item.service_color};">
                            <div class="service-name">${item.service_name}</div>
                            <div class="queue-number">---</div>
                            <div class="counter-name">No one being served</div>
                        </div>
                    `;
                }
            });

            // Page Indicator for Serving
            if (maxPages > 1) {
                 const currentPage = servingPageIndex + 1;
                 // We can append a small indicator outside or inside?
                 // Container is grid/flex. 
                 // Adding a div might break layout if not careful.
                 // Actually container is inside .now-serving-section which is flex-col.
                 // Wait, container IS #servingCards.
                 // If I append a non-card div, it becomes a grid item.
                 // So I shouldn't append status text inside #servingCards if it uses grid logic.
                 // But previous CSS change allows wrapping.
                 // It might look odd as a card.
                 // Better: Don't show text inside the grid.
                 // Users just see it cycle.
            }
            
            container.innerHTML = html;
        }
        
        // Global state for cycling upcoming list
        let upcomingQueueData = [];
        let upcomingPageIndex = 0;
        let upcomingCycleStarted = false;

        function renderUpcomingList(services) {
            let allUpcoming = [];
            
            services.forEach(service => {
                if (service.next_up && service.next_up.length > 0) {
                    service.next_up.forEach(item => {
                        allUpcoming.push({
                            ...item,
                            service_name: service.name,
                            service_color: service.color
                        });
                    });
                }
            });
            
            // Limit to max 50 to prevent memory issues, but allow cycling through them
            upcomingQueueData = allUpcoming.slice(0, 50);
            
            // Start the cycle interval once
            if (!upcomingCycleStarted) {
                renderUpcomingView(); // Render immediately
                setInterval(cycleUpcoming, 5000); // Switch page every 5 seconds
                upcomingCycleStarted = true;
            }
            // Note: If data updates in background, upcomingQueueData updates. 
            // The next cycle tick (or fast refresh if we wanted) will pick it up.
            // We won't force re-render here to avoid jitter reset, unless it's empty.
            if (upcomingQueueData.length === 0) renderUpcomingView();
        }

        function cycleUpcoming() {
            if (upcomingQueueData.length === 0) return;
            
            const pageSize = 4;
            const maxPages = Math.ceil(upcomingQueueData.length / pageSize);
            
            upcomingPageIndex++;
            if (upcomingPageIndex >= maxPages) {
                upcomingPageIndex = 0;
            }
            renderUpcomingView();
        }
        
        function renderUpcomingView() {
            const container = document.getElementById('upcomingList');
            
            if (upcomingQueueData.length === 0) {
                container.innerHTML = `
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: rgba(255,255,255,0.4); min-height: 200px;">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No patients in queue</p>
                    </div>
                `;
                return;
            }
            
            const pageSize = 4;
            const start = upcomingPageIndex * pageSize;
            const slice = upcomingQueueData.slice(start, start + pageSize);
            
            // If slice is empty (e.g. data count shrank), reset to page 0
            if (slice.length === 0) {
                upcomingPageIndex = 0;
                // Recursive retry once
                const newStart = 0;
                const newSlice = upcomingQueueData.slice(newStart, newStart + pageSize);
                renderItems(container, newSlice, newStart);
                return;
            }
            
            renderItems(container, slice, start);
        }
        
        function renderItems(container, slice, startOffset) {
            let html = '';
            slice.forEach((item, index) => {
                const globalPosition = startOffset + index + 1;
                const priorityClass = item.priority === 'EMG' ? 'priority-emergency' 
                    : item.priority === 'SNR' ? 'priority-senior'
                    : item.priority === 'PWD' ? 'priority-pwd'
                    : 'priority-regular';
                
                // Add fade-in animation
                html += `
                    <div class="upcoming-item animate-fadeIn">
                        <div class="position">${globalPosition}</div>
                        <div class="queue-info">
                            <div class="queue-number">${item.queue_number}</div>
                            <div class="service-name">${item.service_name}</div>
                        </div>
                        <span class="priority-badge ${priorityClass}">${item.priority || 'REG'}</span>
                    </div>
                `;
            });
            
            // Add visual helper for multiple pages
            const total = upcomingQueueData.length;
            if (total > 4) {
                const totalPages = Math.ceil(total / 4);
                const currentPage = upcomingPageIndex + 1;
                html += `
                    <div class="text-center mt-2" style="color: rgba(255,255,255,0.3); font-size: 0.8rem;">
                        Page ${currentPage} of ${totalPages} (Total: ${total})
                    </div>
                `;
            }
            
            container.innerHTML = html;
        }
        
        // Initial load and refresh every 2 seconds (Real-time)
        loadDisplayData();
        setInterval(loadDisplayData, 2000);
        
    </script>

    <!-- Start Permission Overlay -->
    <div id="startOverlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: opacity 0.5s;">
        <div class="text-center text-white animate-pulse">
            <i class="fas fa-volume-up fa-4x mb-4" style="color: var(--hc-primary);"></i>
            <h1 class="display-4 fw-bold mb-3">Click to Enable Sound</h1>
            <p class="h4 text-white-50">Browser policy requires interaction to play audio</p>
        </div>
    </div>

    <script>
        document.getElementById('startOverlay').addEventListener('click', function() {
            initAudio();
            if (audioCtx && audioCtx.resume) {
                audioCtx.resume();
            }
            this.style.opacity = '0';
            setTimeout(() => {
                this.style.display = 'none';
            }, 500);
        });
    </script>

</body>
</html>

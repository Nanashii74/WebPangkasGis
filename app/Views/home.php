<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>GIS Barbershop Medan Maimun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Gnf4IfcS8T4nY2/wjV9B8oHYgiYDp6gk7U+zY6IYgHpMZ4LRzOdzKs5pF9V3k1r2" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="/assets/css/home-polish.css">
    <style>
        :root {
            --bg: #f9fafb;
            --surface: #ffffff;
            --surface-soft: #f3f4f6;
            --primary: #6b7280;
            --primary-soft: #e5e7eb;
            --text: #222222;
            --muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 18px 40px rgba(0, 0, 0, 0.08);
        }

        body {
            background: radial-gradient(circle at top, rgba(107, 114, 128, 0.08), transparent 35%),
                radial-gradient(circle at bottom left, rgba(107, 114, 128, 0.04), transparent 30%),
                var(--bg);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page-shell {
            min-height: 100vh;
            padding: 1.5rem 1rem 2rem;
        }

        .topbar {
            padding: 1.4rem 1.4rem 1rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            border-radius: 28px;
            border: 1px solid rgba(107, 114, 128, 0.2);
            box-shadow: 0 24px 60px rgba(107, 114, 128, 0.15);
        }

        .topbar .title {
            font-weight: 800;
            letter-spacing: -0.05em;
            font-size: 1.65rem;
            color: #ffffff;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.98rem;
            max-width: 620px;
        }

        .layout-shell {
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            gap: 1.25rem;
            align-items: start;
        }

        .sidebar-panel {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 28px;
            border: 1px solid rgba(107, 114, 128, 0.12);
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
            padding: 1.4rem;
            position: sticky;
            top: 1.5rem;
            max-height: calc(100vh - 3rem);
            overflow: auto;
        }

        .sidebar-panel h5 {
            font-size: 1.15rem;
            letter-spacing: -0.03em;
            margin-bottom: 0.35rem;
        }

        .sidebar-panel .sidebar-note {
            color: var(--muted);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .filter-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1.35rem;
        }

        .filter-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 16px;
            border: 1px solid rgba(107, 114, 128, 0.18);
            background: #ffffff;
            color: #5b5b5b;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease;
        }

        .filter-button.active,
        .filter-button:hover {
            background: var(--primary);
            color: #ffffff;
            border-color: rgba(107, 114, 128, 0.35);
            box-shadow: 0 18px 32px rgba(107, 114, 128, 0.18);
            transform: translateY(-1px);
        }

        .store-card {
            background: #ffffff;
            border: 2px solid rgba(107, 114, 128, 0.13);
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease, background .2s ease;
            overflow: hidden;
            position: relative;
        }

        .store-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform .25s ease;
        }

        .store-card:hover {
            transform: translateY(-2px);
            border-color: rgba(107, 114, 128, 0.22);
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.09);
        }

        .store-card.active {
            background: linear-gradient(to right, rgba(107, 114, 128, 0.04), #ffffff);
            transform: translateY(-3px);
            border-color: rgba(107, 114, 128, 0.42);
            box-shadow: 0 28px 56px rgba(107, 114, 128, 0.14);
        }

        .store-card.active::before {
            transform: scaleY(1);
        }

        .store-card-body {
            padding: 1.35rem;
        }

        .store-card h6 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .store-card .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .rating-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.42rem 0.85rem;
            border-radius: 999px;
            background: rgba(107, 114, 128, 0.14);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.9rem;
            transition: all .2s ease;
        }

        .store-card.active .rating-pill {
            background: rgba(107, 114, 128, 0.24);
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(107, 114, 128, 0.15);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.32rem 0.85rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin-top: 0.6rem;
        }

        .status-buka {
            color: #1a7f4d;
            background: rgba(26, 127, 77, 0.12);
        }

        .status-tutup {
            color: #b0422a;
            background: rgba(255, 99, 71, 0.12);
        }

        .store-card .address {
            color: #5c5c5c;
            font-size: 0.94rem;
            line-height: 1.7;
            margin-top: 0.8rem;
        }

        .store-card .schedule {
            margin-top: 0.85rem;
            color: #8a8a8a;
            font-size: 0.86rem;
        }

        .store-card .coords {
            margin-top: 0.9rem;
            color: var(--muted);
            font-size: 0.85rem;
        }

        .store-card .badge {
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            padding: 0.38rem 0.75rem;
            border-radius: 999px;
            text-transform: uppercase;
            background: rgba(107, 114, 128, 0.15);
            color: #5b6370;
            font-weight: 700;
        }

        .no-data {
            color: var(--muted);
            font-size: 0.95rem;
            text-align: center;
            padding: 1.4rem 0;
        }

        .map-panel {
            min-height: 78vh;
            border-radius: 28px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
            border: none;
            padding: 0;
        }

        #map {
            height: 78vh;
            min-height: 520px;
            width: 100%;
        }

        .store-list {
            display: grid;
            gap: 1rem;
        }

        .store-card {
            background: #ffffff;
            border: 1px solid rgba(107, 114, 128, 0.13);
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .store-card:hover,
        .store-card.active {
            transform: translateY(-1px);
            border-color: rgba(107, 114, 128, 0.28);
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.12);
        }

        .store-card .rating {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 700;
            color: var(--primary);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.32rem 0.85rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin-top: 0.6rem;
        }

        .status-buka {
            color: #1a7f4d;
            background: rgba(26, 127, 77, 0.12);
        }

        .status-tutup {
            color: #b0422a;
            background: rgba(255, 99, 71, 0.12);
        }

        .store-card .address {
            color: #5c5c5c;
            font-size: 0.94rem;
            line-height: 1.7;
            margin-top: 0.8rem;
        }

        .store-card .schedule {
            margin-top: 0.75rem;
            color: #8a8a8a;
            font-size: 0.86rem;
        }

        .store-card .distance {
            margin-top: 0.75rem;
            color: #8a8a8a;
            font-size: 0.86rem;
            min-height: 1.2em;
            display: flex;
            align-items: center;
        }

        .store-card .distance.loading {
            color: #6b7280;
            font-style: italic;
        }

        .store-card .distance.loading::after {
            content: '';
            display: inline-block;
            width: 4px;
            height: 4px;
            margin-left: 4px;
            background-color: #6b7280;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .store-card .route-info {
            margin-top: 0.7rem;
            padding: 0.7rem;
            background: rgba(107, 114, 128, 0.08);
            border-left: 3px solid #6b7280;
            border-radius: 4px;
            color: #4b5563;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .store-card .route-info strong {
            display: block;
            margin-bottom: 0.3rem;
            color: #222222;
        }

        .store-card .route-button {
            display: none;
            margin-top: 0.7rem;
            padding: 0.5rem 1rem;
            background: #6b7280;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            width: 100%;
            position: relative;
        }

        .store-card .route-button:hover:not(:disabled) {
            background: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .store-card .route-button:disabled {
            background: #c5cad1;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .store-card .route-button.loading {
            pointer-events: none;
        }

        .store-card .route-button.loading::after {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: translateY(-50%) rotate(360deg);
            }
        }

        .store-card.active .route-button {
            display: block;
        }

        .store-card .badge {
            font-size: 0.75rem;
            letter-spacing: 0.02em;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            text-transform: uppercase;
            background: rgba(107, 114, 128, 0.12);
            color: #4b5563;
            font-weight: 700;
        }

        .store-card .route-info {
            margin-top: 0.7rem;
            color: #4b5563;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .store-card .coords {
            display: none;
            color: var(--muted);
            font-size: 0.86rem;
        }

        .active .store-card-body {
            position: relative;
        }

        /* User Location & Routing Styles */
        .user-location-marker {
            background: #4CAF50;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .user-location-marker::before {
            content: '';
            font-size: 12px;
        }

        .leaflet-routing-container {
            background: white !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        .leaflet-routing-alternatives-container {
            display: none;
        }

        .store-card .route-button {
            display: none;
            margin-top: 0.7rem;
            padding: 0.5rem 1rem;
            background: #6b7280;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            width: 100%;
        }

        .store-card .route-button:hover:not(:disabled) {
            background: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .store-card .route-button:disabled {
            background: #c5cad1;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .store-card .route-button.loading {
            pointer-events: none;
        }

        .store-card.active .route-button {
            display: block;
        }

        .store-card-body {
            padding: 1.35rem;
            transition: transform .2s ease;
        }

        .store-card-body::after {
            content: '';
            position: absolute;
            top: 1rem;
            right: 1.2rem;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: #ffffff;
            border-radius: 50%;
            font-weight: 800;
            font-size: 0.85rem;
            opacity: 0;
            transform: scale(0);
            transition: all .25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .store-card.active .store-card-body::after {
            opacity: 1;
            transform: scale(1);
        }

        .leaflet-control-scale-line {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid rgba(34, 34, 34, 0.08) !important;
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.09);
        }

        /* Sembunyikan panel instruksi turn-by-turn dari leaflet-routing-machine */
        .leaflet-routing-container {
            display: none !important;
        }

        @media (max-width: 1199px) {
            .layout-shell {
                grid-template-columns: 1fr;
            }

            .sidebar-panel {
                position: relative;
                top: 0;
                max-height: none;
                overflow: visible;
            }

            #map {
                min-height: 46vh;
            }
        }

        @media (max-width: 767px) {
            .page-shell {
                padding: 1rem 0.75rem 1.25rem;
            }

            .topbar {
                padding: 1rem 1rem 0.9rem;
            }

            .topbar .title {
                font-size: 1.4rem;
            }

            #map {
                min-height: 40vh;
            }
        }

        :root {
            --bg: #f4f6f8;
            --surface: #ffffff;
            --surface-soft: #f7f8fa;
            --primary: #1f2937;
            --primary-soft: #eef2f7;
            --accent: #2563eb;
            --success: #16805a;
            --danger: #b9472f;
            --text: #17202a;
            --muted: #687385;
            --border: #dde3ea;
            --shadow: 0 18px 45px rgba(22, 32, 45, 0.1);
        }

        * {
            letter-spacing: 0;
        }

        body {
            background:
                linear-gradient(180deg, #eef2f6 0%, #f8fafc 42%, #f4f6f8 100%);
            color: var(--text);
        }

        .page-shell {
            max-width: 1480px;
            margin: 0 auto;
            padding: 1rem;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(221, 227, 234, 0.95);
            border-radius: 18px;
            box-shadow: 0 14px 36px rgba(31, 41, 55, 0.08);
            backdrop-filter: blur(16px);
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: 14px;
            background: #1f2937;
            color: #ffffff;
            font-weight: 800;
            font-size: 1rem;
        }

        .topbar-main {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            min-width: 0;
        }

        .topbar .title {
            color: var(--text);
            font-size: 1.15rem;
            line-height: 1.2;
            letter-spacing: 0;
        }

        .subtitle {
            color: var(--muted);
            font-size: 0.88rem;
            max-width: none;
            margin-top: 0.16rem;
        }

        .topbar-stats {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 40px;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #f8fafc;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .stat-pill strong {
            color: var(--text);
            font-size: 0.92rem;
        }

        .layout-shell {
            grid-template-columns: minmax(320px, 390px) minmax(0, 1fr);
            gap: 1rem;
        }

        .sidebar-panel,
        .map-panel {
            border-radius: 18px;
            border: 1px solid rgba(221, 227, 234, 0.95);
            box-shadow: var(--shadow);
        }

        .sidebar-panel {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.92);
            top: 1rem;
            max-height: calc(100vh - 2rem);
        }

        .sidebar-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.8rem;
            margin-bottom: 1rem;
        }

        .sidebar-panel h5 {
            font-size: 1rem;
            margin-bottom: 0.18rem;
            color: var(--text);
        }

        .sidebar-panel .sidebar-note {
            margin-bottom: 0;
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .count-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 32px;
            padding: 0 0.7rem;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 0.82rem;
            font-weight: 800;
        }

        .filter-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.55rem;
            margin-bottom: 0.9rem;
            padding: 0.25rem;
            border-radius: 14px;
            background: #f1f4f8;
        }

        .filter-button {
            width: 100%;
            height: 42px;
            border-radius: 11px;
            border-color: transparent;
            background: transparent;
            color: #566273;
            box-shadow: none;
        }

        .filter-button.active,
        .filter-button:hover {
            background: #ffffff;
            color: var(--primary);
            border-color: rgba(221, 227, 234, 0.8);
            box-shadow: 0 8px 20px rgba(31, 41, 55, 0.08);
            transform: none;
        }

        .store-list {
            gap: 0.7rem;
        }

        .store-card {
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: none;
            background: rgba(255, 255, 255, 0.94);
        }

        .store-card::before {
            width: 3px;
            background: var(--accent);
        }

        .store-card:hover,
        .store-card.active {
            transform: translateY(-1px);
            border-color: rgba(37, 99, 235, 0.35);
            box-shadow: 0 14px 30px rgba(31, 41, 55, 0.09);
        }

        .store-card.active {
            background: #ffffff;
        }

        .store-card-body {
            padding: 1rem;
        }

        .store-card h6 {
            font-size: 0.98rem;
            line-height: 1.3;
            padding-right: 0.25rem;
        }

        .store-card .top-row {
            margin-bottom: 0.75rem !important;
        }

        .store-card .badge,
        .rating-pill,
        .status-pill {
            border-radius: 999px;
            font-size: 0.75rem;
            line-height: 1;
        }

        .store-card .badge {
            padding: 0.42rem 0.62rem;
            background: #eff3f8;
            color: #526071;
            white-space: nowrap;
        }

        .rating-pill {
            padding: 0.44rem 0.68rem;
            background: #fff7e6;
            color: #9a6400;
        }

        .store-card.active .rating-pill {
            transform: none;
            background: #fff2cc;
            box-shadow: none;
        }

        .status-pill {
            margin-top: 0;
            padding: 0.44rem 0.68rem;
        }

        .status-buka {
            color: var(--success);
            background: rgba(22, 128, 90, 0.12);
        }

        .status-tutup {
            color: var(--danger);
            background: rgba(185, 71, 47, 0.12);
        }

        .store-card .address {
            margin-top: 0.72rem;
            color: #4f5b6b;
            font-size: 0.88rem;
            line-height: 1.55;
        }

        .store-card .schedule,
        .store-card .distance {
            margin-top: 0.56rem;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .store-card .route-info {
            border-left: 0;
            border-radius: 12px;
            padding: 0.75rem;
            background: #eff6ff;
            color: #27446f;
        }

        .store-card .route-info strong {
            color: #1e3a5f;
        }

        .store-card .route-button {
            min-height: 40px;
            margin-top: 0.8rem;
            border-radius: 11px;
            background: var(--primary);
            box-shadow: none;
        }

        .store-card .route-button:hover:not(:disabled) {
            background: #111827;
            box-shadow: 0 10px 20px rgba(17, 24, 39, 0.16);
        }

        .store-card-body::after {
            top: 0.85rem;
            right: 0.9rem;
            width: 24px;
            height: 24px;
            background: var(--accent);
            font-size: 0.78rem;
        }

        .map-panel {
            position: relative;
            min-height: calc(100vh - 6.25rem);
            background: #dfe7ee;
            overflow: hidden;
        }

        .map-panel::before {
            content: 'Peta Barbershop';
            position: absolute;
            z-index: 450;
            top: 1rem;
            left: 1rem;
            padding: 0.55rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(221, 227, 234, 0.85);
            color: var(--text);
            font-size: 0.82rem;
            font-weight: 800;
            box-shadow: 0 10px 24px rgba(31, 41, 55, 0.12);
            backdrop-filter: blur(14px);
        }

        #map {
            height: calc(100vh - 6.25rem);
            min-height: 560px;
        }

        .leaflet-control-zoom {
            border: 0 !important;
            box-shadow: 0 12px 28px rgba(31, 41, 55, 0.16) !important;
        }

        .leaflet-control-zoom a {
            width: 38px !important;
            height: 38px !important;
            line-height: 38px !important;
            border: 0 !important;
            color: var(--text) !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 14px !important;
            box-shadow: 0 16px 40px rgba(31, 41, 55, 0.18) !important;
        }

        .leaflet-popup-content {
            margin: 0.85rem 1rem !important;
            color: var(--text);
            line-height: 1.45;
        }

        .barber-marker {
            background: transparent;
            border: 0;
        }

        .barber-marker-pin {
            position: relative;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #ffffff;
            border: 2px solid #1f2937;
            box-shadow: 0 12px 28px rgba(31, 41, 55, 0.24);
            color: #1f2937;
            font-weight: 900;
            font-size: 0.82rem;
            transform: rotate(-45deg);
            transition: transform .2s ease, border-color .2s ease, background .2s ease;
        }

        .barber-marker-pin span {
            transform: rotate(45deg);
        }

        .barber-marker-pin::after {
            content: '';
            position: absolute;
            right: 5px;
            top: 5px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--danger);
            box-shadow: 0 0 0 2px #ffffff;
        }

        .barber-marker-pin.is-open::after {
            background: var(--success);
        }

        .barber-marker-pin.is-active {
            background: #2563eb;
            border-color: #ffffff;
            color: #ffffff;
            transform: rotate(-45deg) scale(1.12);
        }

        .barber-marker-pin.is-active::after {
            box-shadow: 0 0 0 2px #2563eb;
        }

        .map-popup {
            min-width: 220px;
            max-width: 260px;
        }

        .map-popup-title {
            margin-bottom: 0.45rem;
            color: var(--text);
            font-size: 0.98rem;
            font-weight: 850;
            line-height: 1.25;
        }

        .map-popup-address {
            margin-bottom: 0.65rem;
            color: #526071;
            font-size: 0.83rem;
        }

        .map-popup-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .map-popup-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.38rem 0.56rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: #445064;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .map-popup-pill.is-open {
            background: rgba(22, 128, 90, 0.12);
            color: var(--success);
        }

        .map-popup-pill.is-closed {
            background: rgba(185, 71, 47, 0.12);
            color: var(--danger);
        }

        .no-data {
            border-radius: 14px;
            background: var(--surface-soft);
        }

        @media (max-width: 1199px) {
            .topbar {
                align-items: flex-start;
            }

            .layout-shell {
                grid-template-columns: 1fr;
            }

            .sidebar-panel {
                position: relative;
                top: 0;
                max-height: none;
            }

            .map-panel,
            #map {
                min-height: 520px;
                height: 58vh;
            }
        }

        @media (max-width: 767px) {
            .page-shell {
                padding: 0.75rem;
            }

            .topbar {
                display: block;
                border-radius: 16px;
            }

            .topbar-stats {
                justify-content: flex-start;
                margin-top: 0.85rem;
            }

            .topbar .title {
                font-size: 1.05rem;
            }

            .subtitle {
                font-size: 0.82rem;
            }

            .sidebar-panel,
            .map-panel {
                border-radius: 16px;
            }

            .map-panel,
            #map {
                min-height: 430px;
                height: 55vh;
            }
        }

        .user-location-marker::before {
            content: '';
        }

        @media (max-width: 767px) {
            .filter-group {
                gap: 0.4rem;
            }

            .store-card-body {
                padding: 0.9rem;
            }

            .store-card .top-row,
            .store-card .d-flex {
                align-items: flex-start !important;
            }

            .store-card .d-flex {
                flex-wrap: wrap;
            }

            .map-panel::before {
                top: 0.75rem;
                left: 0.75rem;
                font-size: 0.78rem;
            }
        }
    </style>
</head>

<body>
    <?php
    function h($value)
    {
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    $barbershops = $barbershops ?? [];
    ?>
    <div class="page-shell">
        <div class="topbar">
            <div class="topbar-main">
                <div class="brand-mark">GIS</div>
                <div>
                    <div class="title">GIS Barbershop Medan Maimun</div>
                    <div class="subtitle">Peta interaktif untuk menemukan barbershop terbaik di sekitar Medan Maimun.</div>
                </div>
            </div>
            <div class="topbar-stats">
                <div class="stat-pill"><strong><?= count($barbershops) ?></strong> Barber</div>
                <div class="stat-pill"><strong>Live</strong> Map</div>
            </div>
        </div>

        <main class="layout-shell">
            <aside class="sidebar-panel">
                <div class="sidebar-header">
                    <div>
                        <h5>Pilih Barber</h5>
                        <p class="sidebar-note">Klik salah satu barber untuk melihat detail di peta.</p>
                    </div>
                    <span class="count-chip"><?= count($barbershops) ?></span>
                </div>
                <div class="filter-group">
                    <!-- Rating -->
                    <button type="button" class="filter-button active" data-filter="rating" title="Urutkan rating">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </button>
                    <!-- A-Z -->
                    <button type="button" class="filter-button" data-filter="alpha" title="Urutkan A-Z">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7h4l-4 6h4M13 6l4 12M17 6l-4 12M21 10h-4M21 18h-4"/>
                        </svg>
                    </button>
                    <!-- Jarak -->
                    <button type="button" class="filter-button" data-filter="distance" title="Urutkan jarak">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="10" r="3"/>
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                        </svg>
                    </button>
                </div>
                <div class="store-list" id="storeList"></div>
                <div class="no-data" id="noData" style="display:none;">Data barbershop tidak ditemukan.</div>
            </aside>

            <section class="map-panel">
                <div class="map-toolbar">
                    <button type="button" class="map-tool-button" id="gpsToggleButton">
                        <span class="map-tool-dot"></span>
                        <span>Gunakan Lokasi</span>
                    </button>
                    <button type="button" class="map-tool-button is-danger" id="clearRouteButton" style="display:none;">
                        <span>Hapus Rute</span>
                    </button>
                </div>
                <div class="route-focus-pill" id="routeFocusPill">Rute aktif</div>
                <div id="map"></div>
            </section>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        window.WebPangkasGIS = {
            stores: <?= json_encode($barbershops ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            directionsUrl: '<?= site_url('route/directions') ?>',
        };
    </script>
    <script src="/assets/js/home-map.js"></script>
</body>

</html>


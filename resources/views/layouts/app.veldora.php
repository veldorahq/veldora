<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? env('APP_NAME', 'Veldora')) ?></title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="/css/veldora-ui.css">

    <style>
        :root {
            --bg: #0a0a0c;
            --surface: #111114;
            --surface-hover: #17171c;
            --border: #222228;
            --border-hover: #33333f;
            --text: #f0f0f3;
            --text-muted: #8c8c9a;
            --accent: #8b5cf6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            background-image: radial-gradient(circle at 50% 0%, rgba(139, 92, 246, 0.08) 0%, transparent 60%);
            background-repeat: no-repeat;
        }

        /* ── Header ─────────────────────────────────────────── */
        .site-header {
            width: 100%;
            border-bottom: 1px solid var(--border);
            background: rgba(10, 10, 12, 0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .header-container {
            max-width: 1080px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
        }

        .brand-icon {
            width: 22px;
            height: 22px;
            display: block;
            flex-shrink: 0;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-nav a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .header-nav a:hover {
            color: #ffffff;
        }

        .github-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text) !important;
            font-size: 0.8rem !important;
            transition: border-color 0.15s, background 0.15s;
        }

        .github-link:hover {
            border-color: var(--border-hover);
            background: var(--surface-hover);
        }

        /* ── Main Content ───────────────────────────────────── */
        main {
            flex: 1;
            max-width: 1080px;
            width: 100%;
            margin: 0 auto;
            padding: 3rem 1.5rem 4rem;
        }

        /* ── Footer ─────────────────────────────────────────── */
        .site-footer {
            border-top: 1px solid var(--border);
            padding: 1.75rem 1.5rem;
            background: rgba(10, 10, 12, 0.4);
        }

        .footer-container {
            max-width: 1080px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.825rem;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-info {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
        }

        @media (max-width: 640px) {
            .header-nav .hide-mobile {
                display: none;
            }
            .footer-container {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

    <header class="site-header">
        <div class="header-container">
            <a href="/" class="brand-link">
                <img src="/favicon.svg" class="brand-icon" alt="Veldora" aria-hidden="true">
                <span><?= htmlspecialchars(env('APP_NAME', 'Veldora')) ?></span>
            </a>

            <nav class="header-nav">
                <a href="https://veldora.modrao.com/docs" target="_blank" rel="noopener" class="hide-mobile">Documentation</a>
                <a href="https://veldora.modrao.com/components" target="_blank" rel="noopener" class="hide-mobile">Components</a>
                <a href="https://github.com/veldorahq" target="_blank" rel="noopener" class="github-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    GitHub
                </a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div>
                Crafted by
                <svg width="14" height="13" viewBox="0 0 2048 1891" aria-hidden="true"
                     style="display:inline-block;vertical-align:middle;margin:0 2px 1px;">
                    <defs>
                        <linearGradient id="starterVeldoraGrad" gradientUnits="userSpaceOnUse"
                            x1="523.677" y1="1348.24" x2="1622.4" y2="270.409">
                            <stop offset="0" stop-opacity="1" stop-color="rgb(35,0,156)"/>
                            <stop offset="1" stop-opacity="1" stop-color="rgb(95,20,255)"/>
                        </linearGradient>
                    </defs>
                    <path fill="url(#starterVeldoraGrad)" d="M 600.44 684.398 C 589.794 685.67 573.159 689.927 562.177 692.854 C 492.727 711.365 421.966 740.803 367.172 787.954 C 358.619 795.2 350.746 803.215 343.654 811.897 C 330.132 828.342 324.291 844.754 299.506 839.568 C 272.019 833.651 249.954 823.084 230.155 802.404 C 219.146 790.906 204.246 762.849 216.262 747.146 C 222.713 738.716 245.093 731.343 255.45 725.48 C 306.04 696.838 363.5 651.266 378.15 592.399 C 383.485 573.234 372.965 541.997 388.957 527.012 C 423.97 492.928 468.035 462.647 499.23 424.779 C 533.168 383.58 558.574 334.289 591.097 291.166 C 673.22 182.277 787.023 76.1554 903.482 4.77602 C 808.551 112.713 732.951 216.214 683.786 353.18 C 695.07 343.508 718.54 327.796 730.864 319.834 C 821.786 260.428 928.227 229.197 1036.83 230.061 C 1024.61 233.493 1008.1 240.535 996.444 245.679 C 931.924 274.166 871.611 316.077 826.458 370.571 C 898.064 351.248 961.277 361.246 1032.4 376.856 C 1123.2 396.787 1197.29 431.264 1272.08 485.256 C 1253.75 478.076 1244.96 477.658 1226.25 473.732 C 1174.64 462.905 1124.08 461.743 1071.83 468.476 C 1051.37 471.113 1038.3 476.196 1019.17 481.193 C 1044.5 485.774 1075.95 501.079 1097.96 514.329 C 1168.56 556.836 1219.72 630.497 1239.37 710.156 C 1264.52 812.056 1253.8 927.246 1199.08 1017.91 C 1198.12 973.062 1191.6 941.66 1163.09 906.574 C 1164.9 920.848 1164.12 945.435 1162.87 960.005 C 1153.04 1074.53 1077.73 1153.52 1026.5 1249.8 C 990.078 1318.99 974.393 1397.22 981.33 1475.11 C 982.695 1491.69 986.135 1517.12 990.938 1532.83 C 989.792 1445.72 1012.58 1396.35 1057.54 1324.24 L 1104.17 1249.85 L 1257.7 1003.79 C 1324.92 894.006 1390.63 783.314 1454.83 671.74 C 1481.65 625.117 1508.02 577.431 1535.3 531.133 C 1550.41 530.646 1566.71 530.723 1581.84 530.947 C 1682.26 532.436 1783.74 528.986 1884.06 531.258 C 1710.45 823.458 1531.46 1112.43 1347.19 1398.03 C 1258.46 1538.46 1168.47 1678.1 1077.24 1816.92 C 1056.35 1776.66 1035.02 1731.11 1015.11 1690.23 C 976.666 1611.25 934.25 1529.44 915.437 1443.43 C 891.475 1340.89 912.268 1238.69 961.806 1146.56 C 1008 1060.65 1044.03 982.327 1020.65 880.977 C 1019.76 875.784 1018.91 871.509 1017.7 866.37 C 1017.8 873.924 1017.64 883.804 1018.16 891.168 C 1016.12 910.478 1015.81 927.576 1011.16 946.652 C 994.961 1013.07 953.874 1069.45 920.799 1127.81 C 886.252 1188.77 860.871 1265.8 863.295 1336.74 C 868.749 1496.31 974.771 1681.09 1049.16 1818.15 C 911.039 1738.89 754.381 1635.34 708.602 1472.47 C 692.164 1413.98 694.132 1337.25 715.341 1280.04 C 676.108 1319.8 652.408 1361.8 633.791 1414.55 C 630.509 1423.85 625.488 1437.85 623.967 1447.28 C 619.946 1418.64 616.169 1393.72 615.295 1364.71 C 612.031 1279.22 638.641 1195.27 690.554 1127.26 C 703.802 1109.97 718.28 1093.65 733.876 1078.43 C 760.588 1050.79 795.944 1023.23 826.87 1000.88 C 783.246 1019.59 738.164 1044.04 694.378 1063.48 C 689.677 1065.41 683.902 1068.52 679.244 1070.87 C 749.682 1006.73 867.223 892.95 871.075 797.474 C 872.263 768.035 862.368 744.287 842.565 722.889 C 885.918 706.928 911.53 711.433 947.057 727.871 C 919.212 704.513 892.381 689.479 857.524 678.621 C 802.246 661.402 742.586 657.69 685.762 669.175 C 664.768 681.395 676.165 682.345 688.284 684.733 C 717.042 690.399 747.413 702.95 771.465 719.846 C 752.184 728.371 709.49 729.997 669.93 739.674 C 612.439 753.736 541.024 785.022 509.497 837.72 C 506.943 876.193 494.246 890.858 471.244 897.757 452.498 898.649 C 430.073 899.717 404.663 892.006 388.021 876.528 C 374.919 844.811 376.473 838.122 379.943 835.044 C 390.502 825.678 417.772 823.643 431.485 819.614 C 462.237 807.165 497.914 789.354 571.225 714.168 600.44 684.398 z M 1007.06 1136.67 C 1067.86 1065.7 1108.09 984.578 1100.74 888.558 C 1094.91 803.315 1055.4 723.894 990.936 667.819 C 935.623 620.602 855.8 595.53 783.55 597.39 C 851.219 621.197 935.595 659.038 1002.57 727.342 1038.75 812.445 C 1093 942.884 1052.16 1045.51 984.973 1159.73 C 991.609 1153.77 1001.29 1143.72 1007.06 1136.67 z M 432.951 626.797 C 460.816 607.73 478.611 600.184 493.432 599.024 509.023 586.642 C 525.907 573.232 530.985 557.551 544.008 540.73 C 563.419 521.877 575.424 511.866 L 574.798 511.165 C 525.327 531.29 483.017 550.553 449.989 595.488 C 433.628 622.498 L 432.36 625.909 L 432.951 626.797 z"/>
                </svg>
                <strong>Shahriyar Fahim</strong>
            </div>
            <div class="footer-info">
                Veldora v<?= \Veldora\Framework\Foundation\Application::VERSION ?> (PHP v<?= PHP_VERSION ?>)
            </div>
        </div>
    </footer>

    <script>
        function copyCode(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const originalSvg = btn.innerHTML;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                setTimeout(() => {
                    btn.innerHTML = originalSvg;
                }, 2000);
            });
        }
    </script>
</body>
</html>

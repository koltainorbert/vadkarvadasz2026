<?php
/**
 * VadászApró – Dashboard főoldal
 * Valós idejű KPI kártyák, Chart.js grafikonok, függő hirdetések, friss felhasználók
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Dashboard {

    public static function render(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        global $wpdb;

        /* ── KPI adatok ───────────────────────────────── */
        $lc  = wp_count_posts( 'va_listing' );
        $published  = (int)( $lc->publish  ?? 0 );
        $pending    = (int)( $lc->pending  ?? 0 );
        $draft      = (int)( $lc->draft    ?? 0 );

        $auctions_enabled = function_exists('va_auctions_enabled') && va_auctions_enabled();
        $auctions_active  = 0;
        if ( $auctions_enabled ) {
            $ac = wp_count_posts('va_auction');
            $auctions_active = (int)( $ac->publish ?? 0 );
        }

        $uc          = count_users();
        $total_users = (int)( $uc['total_users'] ?? 0 );

        $today_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = %s AND DATE(post_date) = %s",
            'va_listing', current_time('Y-m-d')
        ));

        /* ── 7 napos chart adatok ──────────────────────── */
        $days7 = [];
        for ( $i = 6; $i >= 0; $i-- ) {
            $days7[ date('Y-m-d', strtotime("-{$i} days")) ] = 0;
        }
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(post_date) as d, COUNT(*) as cnt
             FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status IN ('publish','pending')
             AND post_date >= %s
             GROUP BY DATE(post_date)",
            'va_listing', date('Y-m-d', strtotime('-6 days'))
        ), ARRAY_A );
        foreach ( $rows as $row ) {
            if ( isset( $days7[ $row['d'] ] ) ) $days7[ $row['d'] ] = (int) $row['cnt'];
        }

        /* ── Top kategóriák ────────────────────────────── */
        $top_cats = get_terms(['taxonomy' => 'va_category', 'orderby' => 'count', 'order' => 'DESC', 'number' => 7, 'hide_empty' => false]);
        if ( is_wp_error($top_cats) ) $top_cats = [];

        /* ── Függő jóváhagyás ──────────────────────────── */
        $pending_posts = get_posts([
            'post_type'      => 'va_listing',
            'post_status'    => 'pending',
            'posts_per_page' => 10,
            'no_found_rows'  => true,
        ]);

        /* ── Legutóbbi felhasználók ────────────────────── */
        $recent_users = get_users(['number' => 6, 'orderby' => 'registered', 'order' => 'DESC']);

        /* ── JSON chart ────────────────────────────────── */
        $chart_labels = wp_json_encode( array_map(fn($d) => date('m.d', strtotime($d)), array_keys($days7)) );
        $chart_data   = wp_json_encode( array_values($days7) );
        $cat_labels   = wp_json_encode( array_map(fn($t) => $t->name, $top_cats) );
        $cat_data     = wp_json_encode( array_map(fn($t) => (int)$t->count, $top_cats) );
        $week_total   = array_sum(array_values($days7));

        /* ── Havi trend (30 nap vs előző 30) ──────────── */
        $this30 = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type=%s AND post_status IN ('publish','pending')
             AND post_date >= %s", 'va_listing', date('Y-m-d', strtotime('-30 days'))
        ));
        $prev30 = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type=%s AND post_status IN ('publish','pending')
             AND post_date BETWEEN %s AND %s",
            'va_listing', date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-31 days'))
        ));
        $trend_pct = $prev30 > 0 ? round(($this30 - $prev30) / $prev30 * 100) : ($this30 > 0 ? 100 : 0);
        $trend_up  = $trend_pct >= 0;

        /* ── Legutóbbi hirdetések ──────────────────────── */
        $recent_listings = get_posts([
            'post_type'   => 'va_listing',
            'post_status' => ['publish','pending'],
            'posts_per_page' => 5,
            'no_found_rows'  => true,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        ?>
        <div class="va-dashboard">

            <!-- ══ TOPBAR breadcrumb ══════════════════════ -->
            <div class="va-db-breadcrumb">
                <span class="va-db-breadcrumb__home">🏠 Irányítópult</span>
                <span class="va-db-breadcrumb__sep">›</span>
                <span class="va-db-breadcrumb__now"><?php echo esc_html(date_i18n('Y. F j., l')); ?></span>
            </div>

            <!-- ══ KPI kártyák ════════════════════════════ -->
            <div class="va-kpi-grid">

                <div class="va-kpi-card va-kpi-card--red">
                    <div class="va-kpi-glow"></div>
                    <div class="va-kpi-top">
                        <span class="va-kpi-icon-wrap">📋</span>
                        <div class="va-kpi-trend <?php echo $trend_up ? 'up' : 'dn'; ?>">
                            <?php echo $trend_up ? '▲' : '▼'; ?>
                            <?php echo abs($trend_pct); ?>%
                        </div>
                    </div>
                    <div class="va-kpi-num"><?php echo number_format($published, 0, ',', '&nbsp;'); ?></div>
                    <div class="va-kpi-label">Aktív hirdetés</div>
                    <div class="va-kpi-sub">+<?php echo $today_count; ?> ma · <?php echo $this30; ?> az elmúlt 30 napban</div>
                </div>

                <div class="va-kpi-card va-kpi-card--orange <?php echo $pending > 0 ? 'va-kpi-card--pulse' : ''; ?>">
                    <div class="va-kpi-glow"></div>
                    <div class="va-kpi-top">
                        <span class="va-kpi-icon-wrap">⏳</span>
                        <?php if ($pending > 0): ?>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=va_listing&post_status=pending')); ?>" class="va-kpi-cta">Kezelés →</a>
                        <?php endif; ?>
                    </div>
                    <div class="va-kpi-num"><?php echo $pending; ?></div>
                    <div class="va-kpi-label">Jóváhagyásra vár</div>
                    <div class="va-kpi-sub"><?php echo $draft; ?> vázlat</div>
                </div>

                <div class="va-kpi-card va-kpi-card--blue">
                    <div class="va-kpi-glow"></div>
                    <div class="va-kpi-top"><span class="va-kpi-icon-wrap">👥</span></div>
                    <div class="va-kpi-num"><?php echo number_format($total_users, 0, ',', '&nbsp;'); ?></div>
                    <div class="va-kpi-label">Regisztrált felhasználó</div>
                    <div class="va-kpi-sub">
                        <?php
                        $new_today = count(get_users(['number'=>999,'date_query'=>[['after'=>'today']]]));
                        echo $new_today > 0 ? "+{$new_today} ma" : 'Nincs ma regisztráció';
                        ?>
                    </div>
                </div>

                <div class="va-kpi-card va-kpi-card--green">
                    <div class="va-kpi-glow"></div>
                    <div class="va-kpi-top"><span class="va-kpi-icon-wrap">🔨</span></div>
                    <div class="va-kpi-num"><?php echo $auctions_active; ?></div>
                    <div class="va-kpi-label">Aktív aukció</div>
                    <div class="va-kpi-sub">
                        <?php echo $auctions_enabled ? 'Aukció funkció aktív' : 'Aukció funkció ki van kapcsolva'; ?>
                    </div>
                </div>

            </div>

            <!-- ══ Grafikonok ═════════════════════════════ -->
            <div class="va-charts-row">

                <div class="va-chart-card va-chart-card--wide">
                    <div class="va-chart-card__hdr">
                        <div>
                            <h3>Hirdetési aktivitás</h3>
                            <p>Az elmúlt 7 nap beérkező hirdetései</p>
                        </div>
                        <div class="va-chart-card__badge"><?php echo $week_total; ?> hirdetés / hét</div>
                    </div>
                    <div class="va-chart-card__body">
                        <canvas id="va-chart-bar" height="160"></canvas>
                    </div>
                </div>

                <div class="va-chart-card">
                    <div class="va-chart-card__hdr">
                        <div>
                            <h3>Kategória eloszlás</h3>
                            <p>Top kategóriák hirdetésszám szerint</p>
                        </div>
                    </div>
                    <div class="va-chart-card__body va-chart-card__body--donut">
                        <canvas id="va-chart-donut" height="160"></canvas>
                        <div class="va-chart-legend" id="va-donut-legend"></div>
                    </div>
                </div>

            </div>

            <!-- ══ Alsó sor ══════════════════════════════ -->
            <div class="va-db-bottom">

                <!-- Függő hirdetések -->
                <div class="va-db-panel va-db-panel--main">
                    <div class="va-db-panel__hdr">
                        <h3>⏳ Jóváhagyásra váró hirdetések</h3>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=va_listing&post_status=pending')); ?>" class="va-btn-link">Összes megtekintése →</a>
                    </div>
                    <?php if ( $pending_posts ): ?>
                    <div class="va-db-table-wrap">
                        <table class="va-db-table">
                            <thead>
                                <tr>
                                    <th>Hirdetés</th>
                                    <th>Hirdető</th>
                                    <th>Kategória</th>
                                    <th>Feladva</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $pending_posts as $p ):
                                $author = get_userdata($p->post_author);
                                $cats   = get_the_terms($p->ID, 'va_category');
                                $cat_name = (!is_wp_error($cats) && $cats) ? esc_html($cats[0]->name) : '–';
                                $price    = get_post_meta($p->ID, 'va_price', true);
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html(wp_trim_words($p->post_title, 7, '…')); ?></strong>
                                        <?php if ($price): ?>
                                        <span class="va-db-price"><?php echo number_format((float)$price, 0, ',', '&nbsp;'); ?> Ft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $author ? esc_html($author->display_name) : '–'; ?></td>
                                    <td><span class="va-db-tag"><?php echo $cat_name; ?></span></td>
                                    <td class="va-db-date"><?php echo esc_html(date_i18n('m.d H:i', strtotime($p->post_date))); ?></td>
                                    <td class="va-db-actions">
                                        <a href="<?php echo esc_url(admin_url("post.php?action=edit&post={$p->ID}")); ?>" class="va-db-btn">Szerkeszt</a>
                                        <a href="<?php echo esc_url(get_preview_post_link($p)); ?>" target="_blank" class="va-db-btn va-db-btn--ghost">Előnézet</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="va-empty-state">
                            <span>🎉</span>
                            <p>Nincs függő hirdetés – minden rendben!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Jobb oldal: felhasználók + gyors műveletek -->
                <div class="va-db-side">

                    <div class="va-db-panel">
                        <div class="va-db-panel__hdr">
                            <h3>👤 Legutóbbi regisztrációk</h3>
                        </div>
                        <ul class="va-user-list">
                        <?php foreach ( $recent_users as $u ): ?>
                            <li class="va-user-list__item">
                                <div class="va-user-avatar-wrap">
                                    <?php echo get_avatar($u->ID, 36, '', '', ['class' => 'va-user-avatar']); ?>
                                </div>
                                <div class="va-user-info">
                                    <strong><?php echo esc_html($u->display_name); ?></strong>
                                    <small><?php echo esc_html($u->user_email); ?></small>
                                </div>
                                <span class="va-user-date"><?php echo esc_html(date_i18n('m.d', strtotime($u->user_registered))); ?></span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="va-db-panel va-db-panel--qa">
                        <div class="va-db-panel__hdr"><h3>⚡ Gyors műveletek</h3></div>
                        <div class="va-qa-grid">
                            <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="va-qa-item">
                                <span class="va-qa-icon">🌐</span>
                                <span>Weboldal megnyitása</span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=va_listing')); ?>" class="va-qa-item">
                                <span class="va-qa-icon">📋</span>
                                <span>Új hirdetés</span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro')); ?>" class="va-qa-item">
                                <span class="va-qa-icon">⚙️</span>
                                <span>Általános beállítások</span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-design')); ?>" class="va-qa-item">
                                <span class="va-qa-icon">🎨</span>
                                <span>Design</span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=va-form-builder')); ?>" class="va-qa-item">
                                <span class="va-qa-icon">🧩</span>
                                <span>Form szerkesztő</span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-stats')); ?>" class="va-qa-item">
                                <span class="va-qa-icon">📈</span>
                                <span>Statisztika</span>
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </div><!-- .va-dashboard -->

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.color = 'rgba(255,255,255,.55)';
            Chart.defaults.font.family = "'Montserrat', system-ui, sans-serif";
            Chart.defaults.font.size = 11;

            const palette = ['#ff2222','#ff6b35','#ffd166','#06d6a0','#118ab2','#073b4c','#9b5de5'];

            // ── Bar chart ──────────────────────────────────────
            const ctxB = document.getElementById('va-chart-bar');
            if (ctxB) {
                new Chart(ctxB, {
                    type: 'bar',
                    data: {
                        labels: <?php echo $chart_labels; ?>,
                        datasets: [{
                            label: 'Hirdetés',
                            data: <?php echo $chart_data; ?>,
                            backgroundColor: function(ctx) {
                                const gradient = ctxB.getContext('2d').createLinearGradient(0, 0, 0, 200);
                                gradient.addColorStop(0, 'rgba(255,0,0,0.7)');
                                gradient.addColorStop(1, 'rgba(255,0,0,0.15)');
                                return gradient;
                            },
                            borderColor: 'rgba(255,60,60,.9)',
                            borderWidth: 1.5,
                            borderRadius: 8,
                            borderSkipped: false,
                            hoverBackgroundColor: 'rgba(255,80,80,.85)',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: {
                            backgroundColor: 'rgba(15,15,15,.95)',
                            borderColor: 'rgba(255,0,0,.4)',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: { label: ctx => ` ${ctx.parsed.y} hirdetés` }
                        }},
                        scales: {
                            x: {
                                grid: { color: 'rgba(255,255,255,.05)', drawBorder: false },
                                ticks: { color: 'rgba(255,255,255,.5)' },
                            },
                            y: {
                                grid: { color: 'rgba(255,255,255,.05)', drawBorder: false },
                                ticks: { color: 'rgba(255,255,255,.5)', stepSize: 1, precision: 0 },
                                beginAtZero: true,
                            }
                        }
                    }
                });
            }

            // ── Doughnut chart ─────────────────────────────────
            const ctxD = document.getElementById('va-chart-donut');
            if (ctxD) {
                const labelsD = <?php echo $cat_labels; ?>;
                const dataD   = <?php echo $cat_data; ?>;
                const total   = dataD.reduce((a,b) => a+b, 0);

                const donutChart = new Chart(ctxD, {
                    type: 'doughnut',
                    data: {
                        labels: labelsD,
                        datasets: [{
                            data: dataD,
                            backgroundColor: palette,
                            borderColor: '#0a0a0a',
                            borderWidth: 3,
                            hoverOffset: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15,15,15,.95)',
                                borderColor: 'rgba(255,0,0,.4)',
                                borderWidth: 1,
                                padding: 10,
                                callbacks: {
                                    label: ctx => ` ${ctx.label}: ${ctx.parsed} db (${total > 0 ? Math.round(ctx.parsed/total*100) : 0}%)`
                                }
                            }
                        }
                    }
                });

                // Custom legend
                const leg = document.getElementById('va-donut-legend');
                if (leg && labelsD.length) {
                    leg.innerHTML = labelsD.map((lbl, i) => {
                        const pct = total > 0 ? Math.round(dataD[i]/total*100) : 0;
                        return `<div class="va-leg-row">
                            <span class="va-leg-dot" style="background:${palette[i % palette.length]}"></span>
                            <span class="va-leg-name">${lbl}</span>
                            <span class="va-leg-val">${dataD[i]}</span>
                            <span class="va-leg-pct">${pct}%</span>
                        </div>`;
                    }).join('');
                }
            }
        });
        </script>
        <?php
    }
}

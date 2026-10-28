{{-- Estilo dos formulários em cartões (Informação Diária, Revisão). Uso: <form class="info-form"> --}}
<style>
    .info-form { color: #16241b; }
    .info-form .info-topo { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    .info-form .info-topo small { display: block; font-size: 13px; font-weight: 600; color: #5b6b60; text-transform: uppercase; letter-spacing: .06em; }
    .info-form .info-topo h3 { margin: 0; }
    .info-form .info-obrig { font-size: 14px; color: #5b6b60; }
    .info-form .info-obrig b, .info-form label b { color: #b3261e; }
    .info-form .info-card { background: #fff; border: 1px solid #dde4de; border-radius: 12px; padding: 20px 22px; margin-bottom: 16px; }
    .info-form .info-card-topo { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .info-form .info-card h4 { margin: 0; font-size: 17px; font-weight: 700; color: #0b5a2a; }
    .info-form .info-grid { display: grid; gap: 14px 16px; margin-bottom: 14px; }
    .info-form .info-grid:last-child { margin-bottom: 0; }
    .info-form .info-g2 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
    .info-form .info-g3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .info-form .info-g4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .info-form label { display: block; margin: 0; font-size: 13px; font-weight: 600; color: #3d4d42; }
    .info-form .info-campo { min-width: 0; }
    .info-form .info-campo .form-control, .info-form .info-campo .chosen-container { margin-top: 6px; }
    .info-form .chosen-container { width: 100% !important; }
    .info-form .info-horas { font-size: 14px; font-weight: 600; color: #0d2b18; background: #fff4c2; border-radius: 999px; padding: 5px 14px; }
    .info-form .info-quadro { border: 1px solid #e6ebe7; border-radius: 10px; padding: 14px 10px 8px; }
    .info-form .info-quadro h5 { margin: 0 8px 8px; font-size: 15px; font-weight: 700; }
    .info-form .info-quadro table { width: 100%; border-collapse: collapse; }
    .info-form .info-quadro th { font-size: 12px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: #5b6b60; padding: 0 8px 6px; }
    .info-form .info-quadro td { padding: 4px 8px; }
    .info-form .info-quadro td:first-child { font-size: 14px; font-weight: 600; white-space: nowrap; width: 1%; padding-right: 16px; }
    .info-form .info-rodape { position: sticky; bottom: 0; z-index: 5; background: #fff; border: 1px solid #dde4de; border-radius: 12px; padding: 12px 18px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 -4px 16px rgba(13, 43, 24, .08); }
    .info-form .info-rodape span { font-size: 14px; color: #5b6b60; }
    .info-form .info-rodape .btn { min-width: 140px; height: 44px; font-weight: 600; }
    @media (max-width: 991px) { .info-form .info-g4 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) { .info-form .info-g2, .info-form .info-g3, .info-form .info-g4 { grid-template-columns: minmax(0, 1fr); } }
</style>

{{-- Stitch DESIGN.md: Technical Precision & Industrial Clarity (.stitch-ui + .purchases-stitch) --}}
<style>
:is(.stitch-ui, .purchases-stitch) {
  --st-surface:#f8f9fa; --st-paper:#ffffff; --st-border:#abb3b7; --st-outline:#737c7f; --st-on:#2b3437; --st-on-var:#586064;
  --st-primary:#5e5e5e; --st-on-primary:#f8f8f8; --st-container:#eaeff1; --st-container-low:#f1f4f6; --st-error:#9f403d;
  font-family:'Inter',system-ui,sans-serif;
  background:var(--st-surface);
  color:var(--st-on);
}
/* Do not use `* { font-family: Inter }` — it overrides Material Symbols and shows icon names as text. */
:is(.stitch-ui, .purchases-stitch) .st-paper { background:var(--st-paper); border:1px solid var(--st-border); border-radius:0; box-shadow:none; }
/* Use background-color only — `background` shorthand resets repeat/position/size and overrides Tailwind,
   which caused custom select chevrons (SVG) to tile across the field. */
:is(.stitch-ui, .purchases-stitch) .st-input, :is(.stitch-ui, .purchases-stitch) .st-select {
  border:1px solid var(--st-outline); border-radius:0; background-color:var(--st-paper); color:var(--st-on);
}
:is(.stitch-ui, .purchases-stitch) .st-input:focus, :is(.stitch-ui, .purchases-stitch) .st-select:focus {
  outline:none; border-width:2px; border-color:var(--st-primary); box-shadow:none;
}
:is(.stitch-ui, .purchases-stitch) textarea.st-input {
  display:block; width:100%; max-width:100%; box-sizing:border-box; resize:vertical; min-height:5rem;
}
/* One chevron per select — overrides Tailwind forms plugin + native arrows (no stacked icons). */
:is(.stitch-ui, .purchases-stitch) select.st-select,
:is(.stitch-ui, .purchases-stitch) select.st-input {
  min-height:2.5rem;
  -webkit-appearance:none;
  -moz-appearance:none;
  appearance:none;
  background-color:var(--st-paper);
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23586064'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") !important;
  background-repeat:no-repeat !important;
  background-position:right 0.625rem center !important;
  background-size:1.125rem !important;
}
:is(.stitch-ui, .purchases-stitch) select.st-select::-ms-expand,
:is(.stitch-ui, .purchases-stitch) select.st-input::-ms-expand {
  display:none;
}
:is(.stitch-ui, .purchases-stitch) .st-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--st-on-var); }
:is(.stitch-ui, .purchases-stitch) .st-label--primary { color:var(--st-primary); }
:is(.stitch-ui, .purchases-stitch) .st-label--error { color:var(--st-error); }
:is(.stitch-ui, .purchases-stitch) .st-btn-primary {
  background:var(--st-primary); color:var(--st-on-primary); border:0; border-radius:0; font-weight:700;
  text-transform:uppercase; letter-spacing:0.05em; font-size:11px;
}
:is(.stitch-ui, .purchases-stitch) .st-btn-primary:hover { opacity:0.92; }
:is(.stitch-ui, .purchases-stitch) .st-btn-secondary {
  background:transparent; color:var(--st-primary); border:1px solid var(--st-primary); border-radius:0; font-weight:700;
  text-transform:uppercase; letter-spacing:0.05em; font-size:11px;
}
:is(.stitch-ui, .purchases-stitch) .st-btn-secondary:hover { background:var(--st-container-low); }
:is(.stitch-ui, .purchases-stitch) .st-btn-primary,
:is(.stitch-ui, .purchases-stitch) .st-btn-secondary {
  min-height:2.75rem;
  min-width:2.75rem;
  touch-action:manipulation;
}
:is(.stitch-ui, .purchases-stitch) .st-touch-target,
:is(.stitch-ui, .purchases-stitch) [data-touch-target] {
  min-height:2.75rem;
  min-width:2.75rem;
  touch-action:manipulation;
}
@media (max-width: 640px) {
  :is(.stitch-ui, .purchases-stitch) .st-sticky-col {
    position: sticky;
    right: 0;
    z-index: 3;
    background: var(--st-paper);
    box-shadow: -6px 0 10px rgba(0, 0, 0, 0.05);
  }
  :is(.stitch-ui, .purchases-stitch) .st-sticky-col.st-thead-cell {
    background: var(--st-container);
    z-index: 4;
  }
}
:is(.stitch-ui, .purchases-stitch) .st-thead { background:var(--st-container); border-bottom:1px solid var(--st-border); }
:is(.stitch-ui, .purchases-stitch) .st-th {
  font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--st-on-var); border-right:1px solid var(--st-border);
}
:is(.stitch-ui, .purchases-stitch) .st-th:last-child { border-right:0; }
:is(.stitch-ui, .purchases-stitch) .st-tr { border-bottom:1px solid var(--st-border); }
:is(.stitch-ui, .purchases-stitch) .st-tr:hover { background:var(--st-container-low); }
:is(.stitch-ui, .purchases-stitch) .st-td { border-right:1px solid var(--st-border); color:var(--st-on); }
:is(.stitch-ui, .purchases-stitch) .st-td:last-child { border-right:0; }
:is(.stitch-ui, .purchases-stitch) .material-symbols-outlined {
  font-family:'Material Symbols Outlined','Material Icons',sans-serif;
  font-weight:normal;
  font-style:normal;
  line-height:1;
  letter-spacing:normal;
  text-transform:none;
  font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;
  -webkit-font-smoothing:antialiased;
  display:inline-block;
  vertical-align:middle;
}
/* Line-item price/qty: hide native number spinners (manual entry only) */
:is(.stitch-ui, .purchases-stitch) .price-input::-webkit-outer-spin-button,
:is(.stitch-ui, .purchases-stitch) .price-input::-webkit-inner-spin-button,
:is(.stitch-ui, .purchases-stitch) .qty-input::-webkit-outer-spin-button,
:is(.stitch-ui, .purchases-stitch) .qty-input::-webkit-inner-spin-button {
  -webkit-appearance:none;
  margin:0;
}
:is(.stitch-ui, .purchases-stitch) .price-input[type='number'],
:is(.stitch-ui, .purchases-stitch) .qty-input[type='number'] {
  -moz-appearance:textfield;
  appearance:textfield;
}
</style>

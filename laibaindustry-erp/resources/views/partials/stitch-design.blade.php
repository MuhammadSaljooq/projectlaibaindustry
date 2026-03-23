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
/* Kill the native dropdown arrow everywhere — otherwise it stacks on top of a custom chevron. */
:is(.stitch-ui, .purchases-stitch) .st-select {
  min-height:2.5rem;
  background-repeat:no-repeat;
  -webkit-appearance:none;
  -moz-appearance:none;
  appearance:none;
}
:is(.stitch-ui, .purchases-stitch) .st-select::-ms-expand {
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
</style>

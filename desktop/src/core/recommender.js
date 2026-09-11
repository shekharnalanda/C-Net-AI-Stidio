export function normalizeHardware(input = {}) {
  return {
    ramGb: Number(input.ramGb || 0),
    vramGb: Number(input.vramGb || 0),
    freeDiskGb: Number(input.freeDiskGb || 0),
    gpuVendor: String(input.gpuVendor || 'unknown').toLowerCase(),
  };
}

export function evaluateEngine(engine, hardwareInput) {
  const hardware = normalizeHardware(hardwareInput);
  const reasons = [];
  if (hardware.ramGb < engine.minRamGb) reasons.push(`Needs ${engine.minRamGb} GB RAM`);
  if (hardware.vramGb < engine.minVramGb) reasons.push(`Needs ${engine.minVramGb} GB VRAM`);
  if (hardware.freeDiskGb < engine.diskGb) reasons.push(`Needs ${engine.diskGb} GB free disk`);
  if (engine.gpu !== 'any' && !hardware.gpuVendor.includes(engine.gpu)) reasons.push(`Needs ${engine.gpu.toUpperCase()} GPU`);
  const compatible = reasons.length === 0;
  const headroom = Math.max(0, hardware.ramGb - engine.minRamGb) + Math.max(0, hardware.vramGb - engine.minVramGb) * 2;
  const score = compatible ? (engine.quality * 20) + (engine.speed * 10) + Math.min(headroom, 20) : -reasons.length * 100;
  return {...engine, compatible, reasons, score};
}

export function recommendEngines(registry, hardware, task) {
  return registry.engines
    .filter(engine => engine.tasks.includes(task))
    .map(engine => evaluateEngine(engine, hardware))
    .sort((a, b) => b.score - a.score)
    .map((engine, index) => ({...engine, recommended: engine.compatible && index === 0}));
}

export function selectEngine(registry, hardware, task, mode = 'auto', manualId = null) {
  const candidates = recommendEngines(registry, hardware, task);
  if (mode === 'manual') {
    const selected = candidates.find(engine => engine.id === manualId);
    if (!selected) throw new Error('Selected engine is not available for this tool.');
    if (!selected.compatible) throw new Error(selected.reasons.join('; '));
    return selected;
  }
  return candidates.find(engine => engine.compatible) || null;
}

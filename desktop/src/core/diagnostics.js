import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {runtimeState} from './runtime-installer.js';

export function validateRegistry(registry){const errors=[];for(const engine of registry.engines||[]){if(!/^[a-z0-9][a-z0-9-]+$/.test(engine.id||''))errors.push('Invalid engine id');for(const item of Object.values(engine.packages||{})){if(!String(item.url||'').startsWith('https://'))errors.push(`${engine.id}: non-HTTPS package`);if(!/^[a-f0-9]{64}$/.test(item.sha256||''))errors.push(`${engine.id}: invalid SHA-256`)}}return errors}
export function buildDiagnostics({version,hardware,registry,runtimeRoot,license,logs=[]}){const registryErrors=validateRegistry(registry),engines=registry.engines.filter(engine=>engine.adapter).map(engine=>{const state=runtimeState(engine,runtimeRoot);return{id:engine.id,installed:Boolean(state),updateAvailable:Boolean(state?.updateAvailable)}});return{generatedAt:new Date().toISOString(),application:{name:'C-Net AI Studio',version},system:{platform:os.platform(),release:os.release(),architecture:os.arch(),ramGb:hardware.ramGb,gpuName:hardware.gpuName,vramGb:hardware.vramGb,freeDiskGb:hardware.freeDiskGb},edition:{name:license.edition,licensed:license.licensed},checks:{registry:registryErrors.length?'FAIL':'PASS',registryErrors,runtimeDirectory:fs.existsSync(runtimeRoot)?'PASS':'NOT_CREATED'},engines,logs}}
export function writeDiagnostics(file,report){fs.writeFileSync(file,JSON.stringify(report,null,2),{mode:0o600});return file}

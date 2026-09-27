<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>MedTwin-X | Research workspace</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #172c32;
            --muted: #52696d;
            --paper: #f4f7f4;
            --surface: #ffffff;
            --line: #d6e1dc;
            --teal: #11645d;
            --mint: #dcefe7;
            --warning: #843b22;
            --warning-bg: #fff0e8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: "Segoe UI Variable Text", "Segoe UI", sans-serif;
            line-height: 1.55;
        }
        .shell { width: min(1120px, calc(100% - 40px)); margin: 0 auto; }
        header { border-bottom: 1px solid var(--line); background: rgba(255,255,255,.82); }
        .masthead { display: flex; align-items: center; justify-content: space-between; min-height: 76px; gap: 20px; }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 700; letter-spacing: .02em; }
        .mark { display: grid; place-items: center; width: 38px; aspect-ratio: 1; border-radius: 10px; background: var(--teal); color: white; font: 700 14px Georgia, serif; }
        .mode { color: var(--teal); font-size: 12px; font-weight: 700; text-transform: uppercase; }
        main { padding: 54px 0 72px; }
        .eyebrow { color: var(--teal); font-size: 12px; font-weight: 700; text-transform: uppercase; }
        h1 { max-width: 760px; margin: 10px 0 14px; font: 500 52px/1.04 Georgia, "Times New Roman", serif; }
        .intro { max-width: 720px; margin: 0; color: var(--muted); font-size: 17px; }
        .notice { display: flex; align-items: flex-start; gap: 14px; margin: 34px 0; padding: 18px 20px; border: 1px solid #efc7b6; border-left: 4px solid var(--warning); background: var(--warning-bg); color: #622b1c; }
        .notice strong { display: block; margin-bottom: 3px; }
        .notice p { margin: 0; }
        .notice-icon { flex: 0 0 auto; display: grid; place-items: center; width: 25px; height: 25px; border: 1px solid currentColor; border-radius: 50%; font-weight: 700; }
        .section-heading { display: flex; align-items: baseline; justify-content: space-between; gap: 20px; margin: 42px 0 14px; }
        h2 { margin: 0; font: 500 25px Georgia, "Times New Roman", serif; }
        .section-heading span { color: var(--muted); font-size: 13px; }
        .metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); border: 1px solid var(--line); background: var(--surface); }
        .metric { padding: 18px 20px; border-right: 1px solid var(--line); }
        .metric:last-child { border-right: 0; }
        .metric small { color: var(--muted); font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .metric strong { display: block; margin-top: 8px; font: 500 30px Georgia, "Times New Roman", serif; }
        .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: 24px 0 10px; }
        label { color: var(--muted); font-size: 13px; font-weight: 600; }
        select { min-width: 210px; padding: 9px 34px 9px 11px; border: 1px solid #a9bcb5; border-radius: 4px; background: white; color: var(--ink); font: inherit; }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); background: white; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 16px; border-bottom: 1px solid #e7eeea; white-space: nowrap; }
        th { background: #edf4f0; color: #425c5b; font-size: 11px; text-transform: uppercase; }
        td { font-size: 14px; }
        tbody tr:last-child td { border-bottom: 0; }
        .notice-line { min-height: 24px; margin-top: 12px; color: var(--muted); font-size: 13px; }
        .notice-line.error { color: var(--warning); }
        .action-button { padding: 10px 14px; border: 0; border-radius: 4px; background: var(--teal); color: white; font: inherit; font-weight: 650; cursor: pointer; }
        .action-button:disabled { opacity: .55; cursor: wait; }
        .experiment-table { margin-top: 14px; }
        footer { padding: 20px 0; border-top: 1px solid var(--line); color: var(--muted); font-size: 12px; }
        @media (max-width: 700px) {
            .shell { width: min(100% - 28px, 560px); }
            main { padding-top: 38px; }
            h1 { font-size: 40px; }
            .metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .metric:nth-child(2) { border-right: 0; }
            .metric:nth-child(-n+2) { border-bottom: 1px solid var(--line); }
            .toolbar { align-items: flex-start; flex-direction: column; }
            select { width: 100%; }
            .section-heading { align-items: flex-start; flex-direction: column; gap: 4px; }
            .mode { text-align: right; }
        }
    </style>
</head>
<body>
    <header>
        <div class="shell masthead">
            <div class="brand"><span class="mark" aria-hidden="true">MX</span><span>MedTwin-X</span></div>
            <span class="mode">Research workspace / Phase 01</span>
        </div>
    </header>
    <main class="shell">
        <span class="eyebrow">Multimodal health trajectory research</span>
        <h1>A governed foundation for longitudinal research.</h1>
        <p class="intro">MedTwin-X studies how structured observations, clinical text, temporal representations, and medical evidence can be connected for reproducible research.</p>
        <section class="notice" aria-label="Safety notice">
            <span class="notice-icon" aria-hidden="true">!</span>
            <div>
                <strong>Research prototype — not a medical diagnosis or treatment system.</strong>
                <p>Not for clinical decisions or personalized medical advice. Use synthetic or approved de-identified data only. Never enter identifiable patient information.</p>
            </div>
        </section>
        <div class="section-heading">
            <h2>Synthetic cohort</h2>
            <span id="dataset-scope">Generated research fixtures only</span>
        </div>
        <section class="metrics" aria-label="Synthetic cohort counts">
            <div class="metric"><small>Subjects</small><strong id="subjects-count">—</strong></div>
            <div class="metric"><small>Timeline events</small><strong id="events-count">—</strong></div>
            <div class="metric"><small>Lab observations</small><strong id="labs-count">—</strong></div>
            <div class="metric"><small>Trained models</small><strong id="models-count">Not available</strong></div>
        </section>
        <div class="toolbar">
            <h2>Patient timeline</h2>
            <label for="patient-select">Synthetic subject <select id="patient-select" disabled><option>Loading cohort</option></select></label>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Event date</th><th>Event</th><th>Research marker A</th><th>Activity index</th></tr></thead>
                <tbody id="timeline-body"><tr><td colspan="4">Loading synthetic timeline…</td></tr></tbody>
            </table>
        </div>
        <div class="section-heading">
            <h2>Versioned digital-twin snapshot</h2>
            <button class="action-button" id="materialize-twin" type="button">Materialize synthetic twin</button>
        </div>
        <section class="metrics" aria-label="Digital-twin snapshot details">
            <div class="metric"><small>Representation</small><strong id="twin-version">Not materialized</strong></div>
            <div class="metric"><small>Timeline records</small><strong id="twin-events">—</strong></div>
            <div class="metric"><small>Missing modalities</small><strong id="twin-missing">—</strong></div>
            <div class="metric"><small>Uncertainty notes</small><strong id="twin-uncertainty">—</strong></div>
        </section>
        <p id="twin-status" class="notice-line" role="status" aria-live="polite">A snapshot is created from the selected synthetic dataset only.</p>
        <div class="section-heading">
            <h2>Structured agent workflow</h2>
            <button class="action-button" id="run-agents" type="button">Analyze synthetic subject</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Agent stage</th><th>Status</th><th>Structured result</th></tr></thead>
                <tbody id="agent-body"><tr><td colspan="3">No agent workflow run selected.</td></tr></tbody>
            </table>
        </div>
        <p id="agent-status" class="notice-line" role="status" aria-live="polite">Evidence retrieval and risk analysis abstain when their required resources are unavailable.</p>
        <div class="section-heading">
            <h2>Baseline experiment</h2>
            <button class="action-button" id="run-experiment" type="button">Run synthetic experiment</button>
        </div>
        <div class="table-wrap experiment-table">
            <table>
                <thead><tr><th>Measured metric</th><th>Value</th><th>Test subjects</th><th>Split</th></tr></thead>
                <tbody id="experiment-body"><tr><td colspan="4">No experiment runs recorded.</td></tr></tbody>
            </table>
        </div>
        <p id="experiment-status" class="notice-line" role="status" aria-live="polite">No model, clinical risk estimate, or medical recommendation is generated.</p>
        <p id="dashboard-status" class="notice-line" role="status" aria-live="polite"></p>
    </main>
    <footer><div class="shell">MedTwin-X research prototype. No clinical validation or patient-care functionality is provided.</div></footer>
    <script>
        const patientSelect = document.getElementById('patient-select');
        const timelineBody = document.getElementById('timeline-body');
        const dashboardStatus = document.getElementById('dashboard-status');
        const experimentBody = document.getElementById('experiment-body');
        const experimentStatus = document.getElementById('experiment-status');
        const runExperimentButton = document.getElementById('run-experiment');
        const agentBody = document.getElementById('agent-body');
        const agentStatus = document.getElementById('agent-status');
        const runAgentsButton = document.getElementById('run-agents');
        const materializeTwinButton = document.getElementById('materialize-twin');
        const twinStatus = document.getElementById('twin-status');

        function showMessage(message, isError = false) {
            dashboardStatus.textContent = message;
            dashboardStatus.classList.toggle('error', isError);
        }

        function addCell(row, value) {
            const cell = document.createElement('td');
            cell.textContent = value ?? '—';
            row.append(cell);
        }

        function renderExperimentMetrics(metrics) {
            experimentBody.replaceChildren();
            const labels = {
                synthetic_static_test_rmse: 'Static carry-forward RMSE',
                synthetic_longitudinal_test_rmse: 'Longitudinal trend RMSE',
                synthetic_centralized_test_rmse: 'Centralized fit RMSE',
                synthetic_federated_test_rmse: 'Simulated federated fit RMSE',
            };
            for (const metric of metrics) {
                const row = document.createElement('tr');
                addCell(row, labels[metric.metric_name] ?? metric.metric_name);
                addCell(row, metric.metric_value);
                addCell(row, metric.sample_count);
                addCell(row, metric.split_name);
                experimentBody.append(row);
            }
            if (!metrics.length) {
                const row = document.createElement('tr');
                addCell(row, 'No experiment runs recorded');
                row.firstChild.colSpan = 4;
                experimentBody.append(row);
            }
        }

        async function loadExperimentHistory() {
            const response = await fetch('/api/research/experiments');
            if (!response.ok) throw new Error('Experiment history is temporarily unavailable.');
            const result = await response.json();
            const latest = result.data[0];
            renderExperimentMetrics(latest ? latest.metrics : []);
            if (latest) experimentStatus.textContent = `Latest computed run #${latest.id} completed ${new Date(latest.completed_at).toLocaleString()}. Synthetic research only.`;
        }

        async function runSyntheticExperiment() {
            runExperimentButton.disabled = true;
            experimentStatus.textContent = 'Running the synthetic-only baseline experiment…';
            try {
                const response = await fetch('/api/research/experiments/synthetic-baseline', { method: 'POST' });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message ?? 'Experiment did not complete.');
                await loadExperimentHistory();
                experimentStatus.textContent = `Run #${result.experiment_run_id} completed from generated arbitrary-unit data. Federated aggregation has no formal privacy guarantee.`;
            } catch (error) {
                experimentStatus.textContent = error.message;
                experimentStatus.classList.add('error');
            } finally {
                runExperimentButton.disabled = false;
            }
        }

        async function runAgentWorkflow() {
            runAgentsButton.disabled = true;
            agentStatus.textContent = 'Running structured stages on the selected synthetic subject…';
            agentStatus.classList.remove('error');
            try {
                const response = await fetch('/api/research/agents/analyze-synthetic', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ patient_id: Number(patientSelect.value) }),
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message ?? 'Agent workflow did not complete.');
                agentBody.replaceChildren();
                for (const run of result.data) {
                    const row = document.createElement('tr');
                    const stageName = run.agent_type.replaceAll('_', ' ');
                    let structuredResult = JSON.stringify(run.structured_result);
                    if (run.agent_type === 'temporal_trend') {
                        const trend = run.structured_result;
                        structuredResult = `${trend.observation_count} observations; slope ${trend.slope_per_30_days} arbitrary units per 30 days.`;
                    } else if (run.structured_result.reason) {
                        structuredResult = run.structured_result.reason;
                    } else if (run.structured_result.summary) {
                        structuredResult = run.structured_result.summary;
                    }
                    addCell(row, stageName);
                    addCell(row, run.status);
                    addCell(row, structuredResult);
                    agentBody.append(row);
                }
                agentStatus.textContent = `Stored ${result.agent_run_ids.length} structured synthetic agent-stage records. This workflow does not make clinical recommendations.`;
            } catch (error) {
                agentStatus.textContent = error.message;
                agentStatus.classList.add('error');
            } finally {
                runAgentsButton.disabled = false;
            }
        }

        async function materializeTwin() {
            materializeTwinButton.disabled = true;
            twinStatus.textContent = 'Materializing the selected synthetic twin snapshot…';
            twinStatus.classList.remove('error');
            try {
                const response = await fetch(`/api/research/digital-twin/${encodeURIComponent(patientSelect.value)}/materialize`, { method: 'POST' });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message ?? 'Digital-twin snapshot could not be created.');
                const state = result.state;
                document.getElementById('twin-version').textContent = state.representation_version;
                document.getElementById('twin-events').textContent = state.temporal_events.length;
                document.getElementById('twin-missing').textContent = state.missing_modalities.length || 'None';
                document.getElementById('twin-uncertainty').textContent = state.uncertainty.length;
                twinStatus.textContent = `Snapshot #${result.digital_twin_state_id} as of ${state.as_of ? new Date(state.as_of).toLocaleString() : 'no timestamp'}; generated synthetic data only.`;
            } catch (error) {
                twinStatus.textContent = error.message;
                twinStatus.classList.add('error');
            } finally {
                materializeTwinButton.disabled = false;
            }
        }

        async function loadTimeline(patientId) {
            timelineBody.replaceChildren();
            const response = await fetch(`/api/research/patients/${encodeURIComponent(patientId)}`);
            if (!response.ok) throw new Error('Synthetic timeline could not be loaded.');
            const result = await response.json();
            for (const event of result.patient.events) {
                const row = document.createElement('tr');
                const lab = event.laboratory_results[0];
                const activity = event.vital_signs[0];
                addCell(row, event.occurred_at ? new Date(event.occurred_at).toLocaleDateString() : '—');
                addCell(row, event.type.replaceAll('_', ' '));
                addCell(row, lab ? `${lab.numeric_value} ${lab.unit}` : '—');
                addCell(row, activity ? `${activity.value} ${activity.unit}` : '—');
                timelineBody.append(row);
            }
            if (!result.patient.events.length) {
                const row = document.createElement('tr');
                addCell(row, 'No timeline events');
                row.firstChild.colSpan = 4;
                timelineBody.append(row);
            }
        }

        async function loadDashboard() {
            try {
                const [overviewResponse, patientsResponse] = await Promise.all([
                    fetch('/api/research/overview'),
                    fetch('/api/research/patients'),
                ]);
                if (!overviewResponse.ok || !patientsResponse.ok) throw new Error('Research data is temporarily unavailable.');
                const overview = await overviewResponse.json();
                const patients = (await patientsResponse.json()).data;
                document.getElementById('subjects-count').textContent = overview.subjects;
                document.getElementById('events-count').textContent = overview.events;
                document.getElementById('labs-count').textContent = overview.laboratory_observations;
                document.getElementById('models-count').textContent = overview.trained_models_available ? 'Available' : 'None';
                patientSelect.replaceChildren();
                for (const patient of patients) {
                    const option = document.createElement('option');
                    option.value = patient.id;
                    option.textContent = `${patient.subject_key} · ${patient.demographics.age_band ?? 'Age not recorded'}`;
                    patientSelect.append(option);
                }
                patientSelect.disabled = patients.length === 0;
                if (patients.length) {
                    await loadTimeline(patientSelect.value);
                    showMessage('Synthetic demonstration data. No predictions or clinical interpretations are generated.');
                } else {
                    timelineBody.innerHTML = '<tr><td colspan="4">No synthetic cohort loaded.</td></tr>';
                    showMessage('No synthetic demonstration cohort is available.', true);
                }
                await loadExperimentHistory();
            } catch (error) {
                timelineBody.innerHTML = '<tr><td colspan="4">Timeline unavailable.</td></tr>';
                showMessage(error.message, true);
            }
        }

        patientSelect.addEventListener('change', () => loadTimeline(patientSelect.value).catch((error) => showMessage(error.message, true)));
        materializeTwinButton.addEventListener('click', materializeTwin);
        runExperimentButton.addEventListener('click', runSyntheticExperiment);
        runAgentsButton.addEventListener('click', runAgentWorkflow);
        loadDashboard();
    </script>
</body>
</html>
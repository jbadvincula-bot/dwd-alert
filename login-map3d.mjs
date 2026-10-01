export default async function run(page, ui) {
  // Install comprehensive error tracking
  await page.evaluate(() => {
    window.__allErrors = [];
    window.__allWarnings = [];
    window.__allRequests = [];
    
    // Capture console messages
    const originalLog = console.log;
    const originalError = console.error;
    const originalWarn = console.warn;
    
    console.log = (...args) => {
      window.__allErrors.push({type: 'log', args: args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ')});
      originalLog.apply(console, args);
    };
    console.error = (...args) => {
      window.__allErrors.push({type: 'error', args: args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ')});
      originalError.apply(console, args);
    };
    console.warn = (...args) => {
      window.__allWarnings.push({type: 'warn', args: args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ')});
      originalWarn.apply(console, args);
    };
    
    // Capture unhandled errors
    window.addEventListener('error', (e) => {
      window.__allErrors.push({
        type: 'uncaught',
        msg: e.message,
        filename: e.filename,
        lineno: e.lineno,
        colno: e.colno
      });
    });
    
    window.addEventListener('unhandledrejection', (e) => {
      window.__allErrors.push({
        type: 'unhandledrejection',
        msg: e.reason ? (typeof e.reason === 'object' ? JSON.stringify(e.reason) : String(e.reason)) : 'unknown'
      });
    });
    
    // Capture network requests
    if (window.performance && performance.getEntriesByType) {
      const entries = performance.getEntriesByType('resource');
      window.__allRequests = entries.map(e => ({
        name: e.name,
        type: e.initiatorType,
        duration: Math.round(e.duration),
        transferSize: e.transferSize,
        status: e.transferSize > 0 ? 'loaded' : 'cached/empty'
      }));
    }
  });

  // Navigate to login
  await page.goto('http://localhost:8000/admin/login');
  await page.waitForTimeout(2000);
  
  // Fill login form
  await page.fill('input[type="email"]', 'admin@waterreport.com');
  await page.fill('input[type="password"]', '123123123');
  await page.click('button[type="submit"]');
  
  // Wait for dashboard
  await page.waitForTimeout(3000);
  
  // Navigate to map-3d
  await page.goto('http://localhost:8000/admin/monitoring/map-3d');
  await page.waitForTimeout(8000);
  
  // Collect results
  const result = await page.evaluate(() => {
    return {
      echo: typeof Echo,
      pusher: typeof Pusher,
      windowEcho: typeof window.Echo,
      errors: window.__allErrors || [],
      warnings: window.__allWarnings || [],
      requests: (window.__allRequests || []).filter(r => r.name.includes('echo') || r.name.includes('pusher') || r.name.includes('maplibre') || r.name.includes('geojson')),
      url: window.location.href
    };
  });
  
  return result;
}

"use strict";
/**
 * registrar-staff-bot.js
 * Simulates a registrar staff member periodically reviewing pending requests.
 * Logs in as acruz, checks the requests list, opens each pending request.
 */
const puppeteer = require("puppeteer");

const BASE_URL    = process.env.REGISTRAR_URL || "http://nmc-registrar:80";
const STAFF_USER  = process.env.BOT_USER      || "acruz";
const STAFF_PASS  = process.env.BOT_PASS      || "staff2025";
const INTERVAL_MS = parseInt(process.env.BOT_INTERVAL || "45000", 10);

async function runBot() {
  console.log(`[bot] Starting — ${BASE_URL} @ ${new Date().toISOString()}`);

  const browser = await puppeteer.launch({
    headless: "new",
    args: [
      "--no-sandbox",
      "--disable-setuid-sandbox",
      "--disable-dev-shm-usage",
      "--disable-gpu",
      "--no-zygote",
      "--single-process",
      "--disable-features=NetworkService,NetworkServiceInProcess",
    ],
  });

  let page;
  let loggedIn = false;

  async function ensureLogin() {
    if (!page || page.isClosed()) {
      page = await browser.newPage();
      page.setDefaultNavigationTimeout(15000);
      loggedIn = false;
    }

    if (loggedIn) return;

    try {
      await page.goto(`${BASE_URL}/staff/login.php`, { waitUntil: "domcontentloaded" });
      await page.type('input[name="username"]', STAFF_USER);
      await page.type('input[name="password"]', STAFF_PASS);
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: "domcontentloaded" });

      const url = page.url();
      if (url.includes("dashboard")) {
        loggedIn = true;
        console.log(`[bot] Logged in as ${STAFF_USER}`);
      } else {
        console.log("[bot] Login failed — will retry next cycle");
      }
    } catch (e) {
      console.log("[bot] Login error:", e.message);
    }
  }

  async function reviewRequests() {
    try {
      await ensureLogin();
      if (!loggedIn) return;

      // Load requests list — collect pending request IDs
      await page.goto(`${BASE_URL}/staff/requests.php`, { waitUntil: "domcontentloaded" });

      const ids = await page.$$eval(
        'a[href*="view_request.php?id="]',
        (links) => links.map((a) => {
          const m = a.href.match(/id=(\d+)/);
          return m ? parseInt(m[1], 10) : null;
        }).filter(Boolean)
      );

      // De-duplicate and take up to 10
      const unique = [...new Set(ids)].slice(0, 10);
      console.log(`[bot] Found ${unique.length} request(s) to review`);

      for (const id of unique) {
        try {
          await page.goto(`${BASE_URL}/staff/view_request.php?id=${id}`, {
            waitUntil: "domcontentloaded",
          });
          // Small delay between requests — realistic reading time
          await new Promise((r) => setTimeout(r, 1500 + Math.random() * 1000));
        } catch (e) {
          console.log(`[bot] Error viewing request ${id}:`, e.message);
          loggedIn = false;
        }
      }
    } catch (e) {
      console.log("[bot] Review error:", e.message);
      loggedIn = false;
    }
  }

  // Initial delay before first run (let registrar container start up)
  await new Promise((r) => setTimeout(r, 10000));

  // Main loop
  while (true) {
    await reviewRequests();
    console.log(`[bot] Sleeping ${INTERVAL_MS / 1000}s`);
    await new Promise((r) => setTimeout(r, INTERVAL_MS));
  }
}

runBot().catch((e) => {
  console.error("[bot] Fatal:", e);
  process.exit(1);
});

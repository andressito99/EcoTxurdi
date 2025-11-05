(() => {
  const DEFAULT_LANG = 'eu'; // Idioma por defecto
  const SUPPORTED = ['eu', 'es', 'en']; // Idiomas soportados
  const STORAGE_KEY = 'lang'; // Clave en localStorage para guardar el idioma
  const BASE_URL = (window.BASE_URL || '').replace(/\/$/, ''); // Base URL de la web

  const cache = {}; // Caché de diccionarios cargados

  // Obtiene el idioma actual desde localStorage o devuelve el por defecto
  function getLang() {
    let lang = localStorage.getItem(STORAGE_KEY);
    if (!lang || !SUPPORTED.includes(lang)) {
      lang = DEFAULT_LANG;
      localStorage.setItem(STORAGE_KEY, lang);
    }
    return lang;
  }

  // Carga el diccionario JSON correspondiente al idioma
  async function loadDict(lang) {
    if (cache[lang]) return cache[lang];
    const path = `${BASE_URL}/assets/i18n/${lang}.json`;
    const res = await fetch(path, { cache: 'no-store' });
    if (!res.ok) throw new Error(`i18n load failed: ${lang}`);
    const data = await res.json();
    cache[lang] = data;
    return data;
  }

  // Aplica el texto traducido a un elemento
  function setText(el, value) {
    if (el.dataset.i18nHtml === 'true') {
      el.innerHTML = value;
    } else {
      el.textContent = value;
    }
  }

  // Aplica el diccionario a todos los elementos del DOM con data-i18n
  function applyDict(dict) {
    // Traduce nodos de texto
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.dataset.i18n;
      if (key && dict[key] !== undefined) setText(el, dict[key]);
    });

    // Traduce atributos como placeholder, title, value, aria-label, alt
    const attrMap = ['placeholder', 'title', 'value', 'ariaLabel', 'alt'];
    attrMap.forEach(attr => {
      const sel = `[data-i18n-${attr.toLowerCase()}]`;
      document.querySelectorAll(sel).forEach(el => {
        // Capitalizar la primera letra para acceder correctamente al dataset
        const datasetKey = `i18n${attr.charAt(0).toUpperCase()}${attr.slice(1)}`;
        const key = el.dataset[datasetKey];
        const val = dict[key];
        if (val !== undefined) {
          const attrName = attr === 'ariaLabel' ? 'aria-label' : attr.toLowerCase();
          el.setAttribute(attrName, val);
        }
      });
    });

    // Actualiza el <title> si existe
    if (dict['app.title']) {
      const t = document.querySelector('head > title');
      if (t) t.textContent = dict['app.title'];
    }

    // Botones "Leer más" y "Volver" en noticias
    if (dict['btn.readmore']) {
      document.querySelectorAll('.tiles .card-actions .btn').forEach(a => {
        a.textContent = dict['btn.readmore'];
      });
    }
    if (dict['btn.back']) {
      const back = document.querySelector('.detalle-noticia a.btn');
      if (back) back.textContent = dict['btn.back'];
    }
  }

  // Aplica un idioma específico a toda la página
  async function applyLanguage(lang) {
    try {
      const dict = await loadDict(lang);
      document.documentElement.setAttribute('lang', lang);
      applyDict(dict);
      window.i18n = { lang, dict };

      const sel = document.getElementById('langSwitcher');
      if (sel && sel.value !== lang) sel.value = lang;

      // Formatea fechas según la localidad
      const locale = lang === 'eu' ? 'eu-ES' : (lang === 'es' ? 'es-ES' : 'en-US');
      const fmt = new Intl.DateTimeFormat(locale, { year: 'numeric', month: 'long', day: '2-digit' });
      document.querySelectorAll('[data-i18n-date]').forEach(el => {
        const iso = el.getAttribute('data-date') || '';
        const d = iso ? new Date(iso) : null;
        if (d && !isNaN(d.getTime())) el.textContent = fmt.format(d);
      });
    } catch (e) {
      console.error(e);
    }
  }

  // Inicializa el sistema de internacionalización
  async function init() {
    const lang = getLang();
    await applyLanguage(lang);

    const sel = document.getElementById('langSwitcher');
    if (sel) {
      sel.addEventListener('change', async (e) => {
        const value = e.target.value;
        if (SUPPORTED.includes(value)) {
          localStorage.setItem(STORAGE_KEY, value);
          await applyLanguage(value);
        }
      });
    }

    // Permite cambiar idioma manualmente desde consola si se desea
    window.setLanguage = async (l) => {
      if (SUPPORTED.includes(l)) {
        localStorage.setItem(STORAGE_KEY, l);
        await applyLanguage(l);
      }
    };
  }

  // Ejecuta init al cargar la página
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
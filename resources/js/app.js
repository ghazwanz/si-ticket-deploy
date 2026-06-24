import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.downloadQrCode = function downloadQrCode(elementId, filename) {
    const container = document.getElementById(elementId);
    if (!container) return;
    const svgElement = container.querySelector('svg');
    if (!svgElement) return;

    const svgString = new XMLSerializer().serializeToString(svgElement);
    const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
    const URL = window.URL || window.webkitURL || window;
    const blobURL = URL.createObjectURL(svgBlob);

    const image = new Image();
    image.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = 400;
        canvas.height = 400;
        const context = canvas.getContext('2d');
        context.fillStyle = '#FFFFFF';
        context.fillRect(0, 0, canvas.width, canvas.height);
        // Draw SVG image onto canvas
        context.drawImage(image, 20, 20, 360, 360);

        const png = canvas.toDataURL('image/png');
        const downloadLink = document.createElement('a');
        downloadLink.href = png;
        downloadLink.download = filename + '.png';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    };
    image.src = blobURL;
};

Alpine.data('checkoutApp', (categories, merchVariants, isSuspended, isCancelled) => ({
    cart: {},
    merchCart: {},
    categories: categories,
    merchVariants: merchVariants,
    isSuspended: isSuspended,
    isCancelled: isCancelled,
    activeMerchModalId: null,
    getMerchItemTotal(itemId) {
        let total = 0;
        this.merchVariants.forEach(mv => {
            if (mv.merchandise_item_id === itemId) {
                total += this.merchCart[mv.id] || 0;
            }
        });
        return total;
    },
    init() {
        this.categories.forEach(c => {
            this.cart[c.id] = 0;
        });
        this.merchVariants.forEach(mv => {
            this.merchCart[mv.id] = 0;
        });
        this.$watch('activeMerchModalId', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
    },
    getQty(id) {
        return this.cart[id] || 0;
    },
    updateQty(id, delta) {
        let current = this.cart[id] || 0;
        let next = current + delta;
        if (next >= 0) {
            this.cart[id] = next;
        }
    },
    getMerchQty(id) {
        return this.merchCart[id] || 0;
    },
    updateMerchQty(id, delta) {
        let current = this.merchCart[id] || 0;
        let next = current + delta;
        if (next >= 0) {
            this.merchCart[id] = next;
        }
    },
    get totalTickets() {
        return Object.values(this.cart).reduce((a, b) => a + b, 0);
    },
    get totalMerch() {
        return Object.values(this.merchCart).reduce((a, b) => a + b, 0);
    },
    get totalPrice() {
        let total = 0;
        this.categories.forEach(c => {
            total += (this.cart[c.id] || 0) * c.price;
        });
        this.merchVariants.forEach(mv => {
            total += (this.merchCart[mv.id] || 0) * mv.price;
        });
        return total;
    },
    getActiveTickets() {
        return Object.entries(this.cart)
            .filter(([_, qty]) => qty > 0)
            .map(([id, qty]) => ({ id: id, qty: qty }));
    },
    getActiveMerch() {
        return Object.entries(this.merchCart)
            .filter(([_, qty]) => qty > 0)
            .map(([id, qty]) => ({ id: id, qty: qty }));
    },
    format(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    }
}));

const initAppLogic = () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const header = document.querySelector('[data-site-header]');
    const hero = document.querySelector('[data-hero]');
    const explicitRevealItems = Array.from(document.querySelectorAll('[data-reveal]'));
    const fallbackRevealItems = Array.from(document.querySelectorAll('main > *:not(script):not(style):not([data-no-reveal])'));
    const revealItems = explicitRevealItems.length > 0 ? explicitRevealItems : fallbackRevealItems;
    const revealDuration = '780ms';
    const revealStaggerStep = 45;
    const revealDelayScale = 0.5;
    const revealExtraDelay = 500;

    if (header) {
        const handleScroll = () => {
            if (window.scrollY > 12) {
                header.dataset.scrolled = 'true';
            } else {
                header.dataset.scrolled = 'false';
            }
        };
        handleScroll();
        window.addEventListener('scroll', handleScroll, { passive: true });
    }

    revealItems.forEach((element, index) => {
        element.style.transitionDuration = revealDuration;
        element.style.transitionTimingFunction = 'cubic-bezier(0.22, 1, 0.36, 1)';
        if (!element.dataset.revealDelay) {
            element.dataset.revealDelay = String(index * revealStaggerStep);
        }
    });

    const animatedRevealItems = revealItems.filter((element) => {
        return element.classList.contains('opacity-0')
            || element.classList.contains('translate-y-6')
            || element.classList.contains('scale-[0.98]')
            || element.classList.contains('blur-sm');
    });

    if (!reduceMotion && animatedRevealItems.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const element = entry.target;
                const delay = Number(element.dataset.revealDelay ?? 0) * revealDelayScale + revealExtraDelay;
                window.setTimeout(() => {
                    element.classList.remove('translate-y-6', 'scale-[0.98]', 'opacity-0', 'blur-sm');
                    element.classList.add('translate-y-0', 'scale-100', 'opacity-100', 'blur-0');
                }, delay);
                observer.unobserve(element);
            });
        }, { threshold: 0.18, rootMargin: '0px 0px -8% 0px' });

        animatedRevealItems.forEach((element) => observer.observe(element));
    } else {
        revealItems.forEach((element) => {
            element.classList.remove('translate-y-6', 'scale-[0.98]', 'opacity-0', 'blur-sm');
            element.classList.add('translate-y-0', 'scale-100', 'opacity-100', 'blur-0');
        });
    }

    // Parallax & Tilt Logic...
    if (!reduceMotion && hero) {
        const parallaxLayers = hero.querySelectorAll('[data-parallax]');
        const setParallax = (clientX, clientY) => {
            const rect = hero.getBoundingClientRect();
            const offsetX = ((clientX - rect.left) / rect.width - 0.5) * 2;
            const offsetY = ((clientY - rect.top) / rect.height - 0.5) * 2;
            parallaxLayers.forEach((layer) => {
                const depth = Number(layer.dataset.parallax ?? 0.05);
                layer.style.transform = `translate3d(${offsetX * depth * 24}px, ${offsetY * depth * 24}px, 0)`;
            });
        };
        hero.addEventListener('mousemove', (e) => window.requestAnimationFrame(() => setParallax(e.clientX, e.clientY)));
        hero.addEventListener('mouseleave', () => parallaxLayers.forEach(l => l.style.transform = 'translate3d(0, 0, 0)'));
    }

    document.querySelectorAll('[data-tilt]').forEach((tiltElement) => {
        tiltElement.style.transitionDuration = revealDuration;
        tiltElement.style.transitionTimingFunction = 'cubic-bezier(0.22, 1, 0.36, 1)';
        tiltElement.addEventListener('mousemove', (event) => {
            const rect = tiltElement.getBoundingClientRect();
            const offsetX = (event.clientX - rect.left) / rect.width - 0.5;
            const offsetY = (event.clientY - rect.top) / rect.height - 0.5;
            tiltElement.style.transform = `rotateY(${offsetX * 6}deg) rotateX(${offsetY * -6}deg)`;
        });
        tiltElement.addEventListener('mouseleave', () => tiltElement.style.transform = 'rotateY(0deg) rotateX(0deg)');
    });
};

// SPA Logic
const mainElement = document.querySelector('main');
const loader = document.getElementById('spa-loader');

function updateActiveLinks(newDoc) {
    const containers = ['aside', 'nav', '#mobile-menu'];
    
    containers.forEach(selector => {
        const currentContainer = document.querySelector(selector);
        const newContainer = newDoc.querySelector(selector);
        
        if (currentContainer && newContainer) {
            const currentLinks = currentContainer.querySelectorAll('a[data-link]');
            const newLinks = newContainer.querySelectorAll('a[data-link]');
            
            const linkMap = new Map();
            newLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href) {
                    linkMap.set(href, link.getAttribute('class'));
                }
            });
            
            currentLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && linkMap.has(href)) {
                    link.setAttribute('class', linkMap.get(href));
                }
            });
        }
    });
}

window.loadPage = function loadPage(url, push = true) {
    // document.body.classList.remove('overflow-hidden', 'overflow-y-hidden');
    if (loader) loader.classList.remove('hidden');
    if (mainElement) mainElement.classList.add('opacity-50');

    const activeElementId = document.activeElement?.id;
    const selectionStart = document.activeElement?.selectionStart;
    const selectionEnd = document.activeElement?.selectionEnd;

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Detect layout mismatch between current DOM and new DOM
            const hasSidebarCurrent = !!document.querySelector('aside');
            const hasSidebarNew = !!doc.querySelector('aside');
            const hasHeaderCurrent = !!document.querySelector('[data-site-header]');
            const hasHeaderNew = !!doc.querySelector('[data-site-header]');

            if (hasSidebarCurrent !== hasSidebarNew || hasHeaderCurrent !== hasHeaderNew) {
                window.location.href = url;
                return;
            }

            const newContent = doc.querySelector('main')?.innerHTML;
            const newModals = doc.querySelector('#spa-modals')?.innerHTML;
            const newHeader = doc.querySelector('#spa-header')?.innerHTML;
            const newTitle = doc.querySelector('title')?.innerText;

            if (newContent) {
                mainElement.innerHTML = newContent;
                
                const modalContainer = document.getElementById('spa-modals');
                if (modalContainer) {
                    modalContainer.innerHTML = newModals || '';
                }

                const headerContainer = document.getElementById('spa-header');
                if (headerContainer && newHeader) {
                    headerContainer.innerHTML = newHeader;
                }

                if (newTitle) document.title = newTitle;
                if (push) history.pushState(null, '', url);
                
                // Re-initialize logic
                initAppLogic();
                updateActiveLinks(doc);
                
                if (window.Alpine) {
                    window.Alpine.initTree(mainElement);
                    if (modalContainer) {
                        window.Alpine.initTree(modalContainer);
                    }
                }

                // Restore focus
                if (activeElementId) {
                    const el = document.getElementById(activeElementId);
                    if (el) {
                        el.focus();
                        if (typeof selectionStart === 'number') {
                            el.setSelectionRange(selectionStart, selectionEnd);
                        }
                    }
                }
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        })
        .catch(err => {
            console.error('Page load failed', err);
            window.location.href = url; // Fallback to full reload
        })
        .finally(() => {
            if (loader) loader.classList.add('hidden');
            if (mainElement) mainElement.classList.remove('opacity-50');
        });
}

function getApiFormMethod(form) {
    const methodInput = form.querySelector('input[name="_method"]');
    const method = (methodInput?.value || form.getAttribute('method') || 'POST').toUpperCase();

    return method === 'GET' ? 'GET' : 'POST';
}

function getJsonMessage(payload) {
    return payload?.message || 'Permintaan berhasil diproses.';
}

window.submitApiForm = async function submitApiForm(form) {
    const formData = new FormData(form);
    const method = getApiFormMethod(form);
    const response = await fetch(form.action, {
        method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: method === 'GET' ? undefined : formData,
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const errorMessage = payload?.message || 'Terjadi kesalahan saat memproses permintaan.';
        throw new Error(errorMessage);
    }

    return payload;
};

document.addEventListener('DOMContentLoaded', () => {
    initAppLogic();
    Alpine.start();

    document.body.addEventListener('click', e => {
        const link = e.target.closest('a[data-link]');
        if (link && link.getAttribute('href') && !link.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            loadPage(link.getAttribute('href'));
        }
    });

    // Global interceptor to prevent double form submission (spamming click)
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form');
        if (!form) return;

        // Skip GET requests and forms targeting a new tab/window
        if ((form.getAttribute('method') || 'GET').toUpperCase() === 'GET' || form.target === '_blank') {
            return;
        }

        // If the submit event has already been prevented, skip
        if (e.defaultPrevented) {
            return;
        }

        // Prevent duplicate submission if already submitting
        if (form.dataset.submitting === 'true') {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        // Mark the form as submitting
        form.dataset.submitting = 'true';

        // Disable all submit buttons inside the form
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach(button => {
            button.disabled = true;
        });

        // Disable any external submit buttons that target this form via the form attribute
        if (form.id) {
            const externalButtons = document.querySelectorAll(`button[type="submit"][form="${form.id}"], input[type="submit"][form="${form.id}"]`);
            externalButtons.forEach(button => {
                button.disabled = true;
            });
        }
    });

    document.body.addEventListener('submit', async (e) => {
        const form = e.target.closest('form[data-api-form]');
        if (!form) return;

        e.preventDefault();

        try {
            const payload = await window.submitApiForm(form);
            const modal = form.closest('[x-data]');
            if (modal && window.Alpine?.$data) {
                // no-op: modal state is reset by reloading below
            }

            window.loadPage(window.location.pathname + window.location.search, false);
        } catch (error) {
            console.error('API form submit failed', error);
            alert(error.message || 'Terjadi kesalahan saat memproses permintaan.');

            // Re-enable form submission on error
            form.dataset.submitting = 'false';
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach(button => {
                button.disabled = false;
            });
            if (form.id) {
                const externalButtons = document.querySelectorAll(`button[type="submit"][form="${form.id}"], input[type="submit"][form="${form.id}"]`);
                externalButtons.forEach(button => {
                    button.disabled = false;
                });
            }
        }
    });

    // document.body.addEventListener('submit', e => {
    //     const form = e.target.closest('form');
    //     if (form && (!form.getAttribute('method') || form.getAttribute('method').toLowerCase() === 'get')) {
    //         const actionAttr = form.getAttribute('action');
    //         const actionUrl = actionAttr ? new URL(actionAttr, window.location.origin) : new URL(window.location.href);
            
    //         // Only intercept local/same-origin GET forms
    //         if (actionUrl.origin === window.location.origin) {
    //             e.preventDefault();
    //             const formData = new FormData(form);
    //             const searchParams = new URLSearchParams();
                
    //             for (const [key, value] of formData.entries()) {
    //                 if (value !== '') {
    //                     searchParams.append(key, value);
    //                 }
    //             }
                
    //             const targetUrl = actionUrl.pathname + (searchParams.toString() ? '?' + searchParams.toString() : '');
    //             loadPage(targetUrl);
    //         }
    //     }
    // });

    window.addEventListener('popstate', () => {
        loadPage(location.pathname + location.search, false);
    });
});

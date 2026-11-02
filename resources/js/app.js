import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

import './shop/helpers';
import { registerCartStore } from './shop/cart';
import { registerUiStore } from './shop/ui';
import { registerAbandonedCartTracker } from './shop/abandoned';

// Page-level Alpine components. Each page drops a module in ./shop/pages and
// registers itself with Alpine.data(...); nothing here needs editing for that.
import.meta.glob('./shop/pages/*.js', { eager: true });

Alpine.plugin(persist);

Alpine.magic('pkr', () => (amount) => window.bsf.pkr(amount));

// Cart first: the UI store's scroll lock watches the drawer's open state.
registerCartStore(Alpine);
registerUiStore(Alpine);
registerAbandonedCartTracker(Alpine);

window.Alpine = Alpine;
Alpine.start();

<x-layouts.app>
    <x-admin-nav />

    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="inventarioUCH()">
        <x-flash-message />

                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-label-md uppercase tracking-wider text-uch-muted mb-4">
                    <span>Control y Operación</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-uch-blue font-bold">Inventario</span>
                </div>

                <!-- Encabezado -->
                <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-headline-lg-mobile lg:text-headline-lg font-rubik text-uch-navy tracking-tight">Inventario y Control de Stock</h1>
                        <p class="text-body-md text-uch-muted mt-1">Existencias de productos y raciones de menú.</p>
                    </div>
                    <div class="flex items-center gap-space-sm shrink-0">
                        <a href="/admin/movimientos"
                           class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-body-sm font-semibold text-uch-blue transition-colors hover:bg-uch-blue/10">
                            <span class="material-symbols-outlined text-[19px]">history</span>
                            Historial de movimientos
                        </a>
                        <button type="button" @click="abrirManual()"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-uch-navy text-white text-body-sm font-semibold shadow-sm hover:bg-uch-navy-dark transition-colors shrink-0">
                            <span class="material-symbols-outlined text-[19px]">add</span>
                            Nuevo ajuste
                        </button>
                    </div>
                </header>

                <!-- Métricas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="flex items-center justify-between gap-4 rounded-card border border-uch-border bg-white p-4 shadow-uch-card">
                        <div>
                            <p class="text-label-md uppercase tracking-wider text-uch-muted">Raciones de menú</p>
                            <p class="mt-1 text-headline-sm font-rubik text-uch-navy">{{ $racionesDisponibles }} <span class="text-body-sm font-sans text-uch-muted">rac.</span></p>
                            <p class="text-body-sm text-uch-teal">{{ $opciones->count() }} opciones con stock controlado</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-uch-cyan/10 flex items-center justify-center text-uch-blue shrink-0">
                            <span class="material-symbols-outlined text-[24px]">soup_kitchen</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-card border border-uch-border bg-white p-4 shadow-uch-card">
                        <div>
                            <p class="text-label-md uppercase tracking-wider text-uch-muted">Alertas de stock</p>
                            <p class="mt-1 text-headline-sm font-rubik text-uch-coral">{{ $alertasStock }} <span class="text-body-sm font-sans text-uch-muted">ítems</span></p>
                            <p class="text-body-sm text-uch-coral">Requieren reposición o revisión</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-uch-coral/10 flex items-center justify-center text-uch-coral shrink-0">
                            <span class="material-symbols-outlined text-[24px]">warning</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-card border border-uch-border bg-white p-4 shadow-uch-card">
                        <div>
                            <p class="text-label-md uppercase tracking-wider text-uch-muted">Última auditoría</p>
                            <p class="mt-1 text-headline-sm font-rubik text-uch-navy">{{ $ultimaAuditoria }}</p>
                            <p class="text-body-sm text-uch-muted">
                                {{ $totalMovimientos }} movimientos registrados ·
                                <a href="/admin/movimientos" class="font-semibold text-uch-blue hover:underline">Ver historial</a>
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-uch-blue/10 flex items-center justify-center text-uch-blue shrink-0">
                            <span class="material-symbols-outlined text-[24px]">published_with_changes</span>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <section class="rounded-card border border-uch-border bg-white p-4 shadow-uch-card mb-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                        <div class="lg:col-span-5">
                            <label for="filtro-buscar" class="block text-body-sm font-semibold text-uch-navy mb-1.5">Buscar ítem</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-uch-muted text-[18px]">search</span>
                                <input id="filtro-buscar" type="text" x-model="filtros.buscar"
                                       placeholder="Nombre, referencia o SKU..."
                                       class="w-full pl-10 pr-3 py-2 rounded-lg border-uch-border bg-uch-surface text-body-sm focus:border-uch-blue focus:ring-uch-blue">
                            </div>
                        </div>
                        <div class="lg:col-span-3">
                            <label for="filtro-tipo" class="block text-body-sm font-semibold text-uch-navy mb-1.5">Tipo</label>
                            <select id="filtro-tipo" x-model="filtros.tipo" class="w-full rounded-lg border-uch-border bg-uch-surface text-body-sm focus:border-uch-blue focus:ring-uch-blue">
                                <option value="todos">Todos</option>
                                <option value="producto">Productos adicionales</option>
                                <option value="opcion_menu">Raciones de menú</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label for="filtro-estado" class="block text-body-sm font-semibold text-uch-navy mb-1.5">Estado</label>
                            <select id="filtro-estado" x-model="filtros.estado" class="w-full rounded-lg border-uch-border bg-uch-surface text-body-sm focus:border-uch-blue focus:ring-uch-blue">
                                <option value="todos">Todos</option>
                                <option value="bajo">Stock bajo</option>
                                <option value="agotado">Agotado</option>
                                <option value="disponible">Disponible</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <button type="button" @click="limpiar()"
                                    class="w-full py-2 px-3 rounded-lg bg-uch-bg text-uch-navy text-body-sm font-semibold hover:bg-uch-border transition-colors">
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Tabla de existencias -->
                <section class="rounded-card border border-uch-border bg-white shadow-uch-card overflow-hidden">
                    <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-uch-border bg-uch-surface">
                        <div>
                            <h2 class="font-rubik text-headline-sm text-uch-navy">Existencias trazables</h2>
                            <p class="text-body-sm text-uch-muted">Productos y raciones en una sola vista.</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-uch-blue/10 text-uch-blue text-body-sm font-semibold" x-text="filtrados.length + ' registros'"></span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[960px] text-left border-collapse">
                            <thead>
                                <tr class="bg-uch-bg text-uch-muted text-label-md uppercase tracking-wider">
                                    <th class="py-3 px-5 font-bold">Referencia (SKU)</th>
                                    <th class="py-3 px-5 font-bold">Producto / Plato</th>
                                    <th class="py-3 px-5 font-bold">Categoría</th>
                                    <th class="py-3 px-5 font-bold">Stock disponible</th>
                                    <th class="py-3 px-5 font-bold">Disponibilidad</th>
                                    <th class="py-3 px-5 font-bold">Alerta</th>
                                    <th class="py-3 px-5 text-right font-bold">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-uch-border">
                                <template x-for="item in paginados" :key="item.tipo + '-' + item.item_id">
                                    <tr class="hover:bg-uch-bg/70 transition-colors">
                                        <td class="py-3 px-5 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded bg-uch-bg text-uch-navy font-mono text-body-sm" x-text="item.referencia"></span>
                                        </td>
                                        <td class="py-3 px-5 min-w-[240px]">
                                            <div class="flex items-center gap-3">
                                                <template x-if="item.imagen">
                                                    <img :src="item.imagen" :alt="item.nombre" class="w-11 h-11 rounded-full object-cover bg-uch-bg shrink-0">
                                                </template>
                                                <template x-if="!item.imagen">
                                                    <div class="w-11 h-11 rounded-full bg-uch-bg flex items-center justify-center text-uch-blue shrink-0">
                                                        <span class="material-symbols-outlined text-[20px]" x-text="item.tipo === 'producto' ? 'local_cafe' : 'restaurant'"></span>
                                                    </div>
                                                </template>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-uch-navy truncate" x-text="item.nombre"></p>
                                                    <p class="text-body-sm text-uch-muted truncate" x-text="item.descripcion"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-5 whitespace-nowrap text-body-sm text-uch-navy" x-text="item.categoria"></td>
                                        <td class="py-3 px-5 min-w-[170px]">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-body-sm font-bold text-uch-navy" x-text="item.stock + ' ' + item.unidad + ' / mín. ' + item.minimo"></span>
                                                <div class="w-full h-2 rounded-full bg-uch-bg overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500"
                                                         :class="item.estado === 'agotado' ? 'bg-uch-coral' : (item.estado === 'bajo' ? 'bg-uch-amber' : 'bg-uch-cyan')"
                                                         :style="'width: ' + porcentaje(item) + '%'"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-5 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-md font-bold" :class="claseEstado(item.estado)">
                                                <span class="w-2 h-2 rounded-full bg-current"></span>
                                                <span x-text="etiquetaEstado(item.estado)"></span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-md font-bold" :class="claseAlerta(item.estado)">
                                                <span class="material-symbols-outlined text-[14px]" x-text="item.estado === 'disponible' ? 'check' : 'report'"></span>
                                                <span x-text="etiquetaAlerta(item.estado)"></span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 whitespace-nowrap text-right">
                                            <button type="button" @click="abrir(item)"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-uch-bg text-uch-blue text-body-sm font-semibold hover:bg-uch-border transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">tune</span>
                                                Gestionar stock
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="paginados.length === 0">
                                    <td colspan="7" class="py-10 text-center text-body-sm text-uch-muted">
                                        No hay ítems que coincidan con los filtros seleccionados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 bg-uch-surface">
                        <p class="text-body-sm text-uch-muted">
                            Mostrando
                            <strong class="text-uch-navy" x-text="paginados.length ? ((pagina - 1) * porPagina + 1) : 0"></strong>
                            -
                            <strong class="text-uch-navy" x-text="Math.min(pagina * porPagina, filtrados.length)"></strong>
                            de
                            <strong class="text-uch-navy" x-text="filtrados.length"></strong>
                            registros
                        </p>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="pagina--" :disabled="pagina === 1"
                                    class="px-2.5 py-1.5 rounded-lg text-uch-muted hover:bg-uch-border disabled:opacity-40 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                            </button>
                            <template x-for="n in totalPaginas" :key="n">
                                <button type="button" @click="pagina = n"
                                        class="w-8 h-8 rounded-lg text-body-sm font-bold transition-colors"
                                        :class="pagina === n ? 'bg-uch-blue text-white shadow-sm' : 'text-uch-navy hover:bg-uch-border'"
                                        x-text="n"></button>
                            </template>
                            <button type="button" @click="pagina++" :disabled="pagina === totalPaginas"
                                    class="px-2.5 py-1.5 rounded-lg text-uch-blue hover:bg-uch-border disabled:opacity-40 transition-colors flex items-center gap-1">
                                <span>Siguiente</span>
                                <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </section>

        <!-- Modal de ajuste trazable -->
        <div x-show="modalAbierto" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-uch-navy/60 backdrop-blur-sm" @click="cerrar()"></div>
            <div class="relative w-full max-w-xl bg-white rounded-card shadow-uch-card-hover p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-label-md uppercase tracking-widest text-uch-blue font-bold">Ajuste de kardex UCH</p>
                        <h3 class="font-rubik text-headline-sm text-uch-navy" x-text="itemNombre || 'Nuevo ajuste de inventario'"></h3>
                        <p class="text-body-sm text-uch-muted mt-0.5">Todo movimiento queda registrado con usuario, motivo y comentario.</p>
                    </div>
                    <button type="button" @click="cerrar()" class="p-2 rounded-lg bg-uch-bg text-uch-muted hover:text-uch-navy transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form method="POST" action="/admin/inventario" class="space-y-4">
                    @csrf
                    <input type="hidden" name="tipo_item" :value="seleccion.split('|')[0]">
                    <input type="hidden" name="item_id" :value="seleccion.split('|')[1]">

                    <div x-show="seleccionManual">
                        <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">Ítem a ajustar</label>
                        <select x-model="seleccion" @change="sincronizarSeleccion()" required class="w-full rounded-lg border-uch-border bg-uch-surface text-body-sm focus:border-uch-blue focus:ring-uch-blue">
                            <option value="">Selecciona un producto o ración</option>
                            <template x-for="item in inventario" :key="item.tipo + '-' + item.item_id">
                                <option :value="item.tipo + '|' + item.item_id" x-text="item.referencia + ' · ' + item.nombre"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3 p-3 rounded-lg bg-uch-bg">
                        <div>
                            <p class="text-label-md uppercase tracking-wider text-uch-muted">Stock actual</p>
                            <p class="font-rubik text-headline-sm text-uch-navy" x-text="stockActual + ' ' + unidad"></p>
                        </div>
                        <div>
                            <p class="text-label-md uppercase tracking-wider text-uch-muted">Mínimo / objetivo</p>
                            <p class="font-rubik text-headline-sm text-uch-navy" x-text="stockMinimo + ' ' + unidad"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-body-sm font-semibold text-uch-navy mb-1.5">Tipo de movimiento</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-lg bg-uch-bg cursor-pointer hover:bg-uch-border transition-colors">
                                <input type="radio" name="operacion" value="ingreso" x-model="operacion" @change="motivo = 'Reposición'" class="text-uch-blue focus:ring-uch-blue">
                                <span class="text-body-sm font-semibold text-uch-navy">Entrada / Producción</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg bg-uch-bg cursor-pointer hover:bg-uch-border transition-colors">
                                <input type="radio" name="operacion" value="salida" x-model="operacion" @change="motivo = 'Merma / Dañado'" class="text-uch-coral focus:ring-uch-coral">
                                <span class="text-body-sm font-semibold text-uch-navy">Merma / Descarte</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="cantidad" class="block text-body-sm font-semibold text-uch-navy mb-1.5">Cantidad</label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="ajustar(-1)" class="w-10 h-10 rounded-lg bg-uch-bg text-uch-navy font-bold hover:bg-uch-border transition-colors">−</button>
                                <input id="cantidad" type="number" name="cantidad" min="1" max="10000" required x-model.number="cantidad"
                                       class="w-full text-center py-2 rounded-lg border-uch-border bg-uch-surface font-rubik text-headline-sm text-uch-navy focus:border-uch-blue focus:ring-uch-blue">
                                <button type="button" @click="ajustar(1)" class="w-10 h-10 rounded-lg bg-uch-bg text-uch-navy font-bold hover:bg-uch-border transition-colors">+</button>
                            </div>
                        </div>
                        <div>
                            <label for="motivo" class="block text-body-sm font-semibold text-uch-navy mb-1.5">Motivo de auditoría</label>
                            <select id="motivo" name="motivo_categoria" x-model="motivo" required class="w-full py-2.5 rounded-lg border-uch-border bg-uch-surface text-body-sm focus:border-uch-blue focus:ring-uch-blue">
                                <template x-for="opcion in motivos" :key="opcion">
                                    <option :value="opcion" x-text="opcion"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="comentario" class="block text-body-sm font-semibold text-uch-navy mb-1.5">Comentario</label>
                        <textarea id="comentario" name="comentario" rows="3" minlength="5" maxlength="500" required x-model="comentario"
                                  placeholder="Describe el motivo del ajuste (mínimo 5 caracteres)..."
                                  class="w-full rounded-lg border-uch-border bg-uch-surface text-body-sm focus:border-uch-blue focus:ring-uch-blue"></textarea>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-lg bg-uch-bg px-4 py-3">
                        <span class="text-body-sm text-uch-muted">Stock proyectado</span>
                        <strong class="font-rubik text-headline-sm" :class="stockProyectado() < 0 ? 'text-uch-coral' : 'text-uch-navy'" x-text="stockProyectado() + ' ' + unidad"></strong>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="cerrar()" class="px-4 py-2.5 rounded-xl bg-uch-bg text-uch-navy text-body-sm font-semibold hover:bg-uch-border transition-colors">Cancelar</button>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-uch-coral text-white text-body-sm font-semibold hover:bg-uch-coral-dark transition-colors">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Confirmar ajuste
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function inventarioUCH() {
                return {
                    inventario: @js($inventario),
                    motivos: @js($motivos),
                    filtros: { buscar: '', tipo: 'todos', estado: 'todos' },
                    pagina: 1,
                    porPagina: 8,
                    modalAbierto: false,
                    seleccionManual: false,
                    seleccion: '',
                    itemNombre: '',
                    stockActual: 0,
                    stockMinimo: 0,
                    unidad: 'unid.',
                    operacion: 'ingreso',
                    motivo: 'Reposición',
                    cantidad: 5,
                    comentario: '',

                    init() {
                        this.$watch('filtros', () => { this.pagina = 1; });
                    },

                    get filtrados() {
                        const buscar = this.filtros.buscar.trim().toLowerCase();

                        return this.inventario.filter((item) => {
                            const coincideTexto = !buscar ||
                                item.nombre.toLowerCase().includes(buscar) ||
                                item.referencia.toLowerCase().includes(buscar) ||
                                item.categoria.toLowerCase().includes(buscar);
                            const coincideTipo = this.filtros.tipo === 'todos' || item.tipo === this.filtros.tipo;
                            const coincideEstado = this.filtros.estado === 'todos' || item.estado === this.filtros.estado;

                            return coincideTexto && coincideTipo && coincideEstado;
                        });
                    },

                    get totalPaginas() {
                        return Math.max(1, Math.ceil(this.filtrados.length / this.porPagina));
                    },

                    get paginados() {
                        if (this.pagina > this.totalPaginas) {
                            this.pagina = this.totalPaginas;
                        }

                        const inicio = (this.pagina - 1) * this.porPagina;
                        return this.filtrados.slice(inicio, inicio + this.porPagina);
                    },

                    limpiar() {
                        this.filtros = { buscar: '', tipo: 'todos', estado: 'todos' };
                        this.pagina = 1;
                    },

                    porcentaje(item) {
                        return Math.min(100, Math.round((item.stock / Math.max(item.minimo, 1)) * 100));
                    },

                    etiquetaEstado(estado) {
                        return { agotado: 'Agotado', bajo: 'Stock bajo', disponible: 'Disponible' }[estado] ?? estado;
                    },

                    claseEstado(estado) {
                        return {
                            agotado: 'bg-uch-coral/10 text-uch-coral-dark',
                            bajo: 'bg-uch-amber/15 text-[#B45309]',
                            disponible: 'bg-uch-teal/15 text-[#008779]',
                        }[estado] ?? 'bg-uch-bg text-uch-muted';
                    },

                    etiquetaAlerta(estado) {
                        return { agotado: 'Sin stock', bajo: 'Stock bajo', disponible: 'Normal' }[estado] ?? estado;
                    },

                    claseAlerta(estado) {
                        return {
                            agotado: 'bg-uch-coral text-white',
                            bajo: 'bg-uch-amber/15 text-[#B45309]',
                            disponible: 'bg-uch-bg text-uch-muted',
                        }[estado] ?? 'bg-uch-bg text-uch-muted';
                    },

                    abrir(item) {
                        this.seleccionManual = false;
                        this.seleccion = item.tipo + '|' + item.item_id;
                        this.itemNombre = item.nombre;
                        this.stockActual = item.stock;
                        this.stockMinimo = item.minimo;
                        this.unidad = item.unidad;
                        this.restablecerFormulario();
                        this.modalAbierto = true;
                    },

                    abrirManual() {
                        this.seleccionManual = true;
                        this.seleccion = '';
                        this.itemNombre = '';
                        this.stockActual = 0;
                        this.stockMinimo = 0;
                        this.unidad = 'unid.';
                        this.restablecerFormulario();
                        this.modalAbierto = true;
                    },

                    cerrar() {
                        this.modalAbierto = false;
                    },

                    restablecerFormulario() {
                        this.operacion = 'ingreso';
                        this.motivo = 'Reposición';
                        this.cantidad = 5;
                        this.comentario = '';
                    },

                    sincronizarSeleccion() {
                        const item = this.inventario.find(
                            (inventario) => inventario.tipo + '|' + inventario.item_id === this.seleccion
                        );

                        if (! item) {
                            return;
                        }

                        this.itemNombre = item.nombre;
                        this.stockActual = item.stock;
                        this.stockMinimo = item.minimo;
                        this.unidad = item.unidad;
                    },

                    ajustar(delta) {
                        this.cantidad = Math.max(1, (Number(this.cantidad) || 1) + delta);
                    },

                    stockProyectado() {
                        const cantidad = Number(this.cantidad) || 0;
                        return this.operacion === 'ingreso' ? this.stockActual + cantidad : this.stockActual - cantidad;
                    },
                };
            }
        </script>
    @endpush
</x-layouts.app>

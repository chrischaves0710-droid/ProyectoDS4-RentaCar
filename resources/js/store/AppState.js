export class AppState {
    constructor(initialState = {}) {
        this.state = {
            data: [],
            pagination: {},
            loading: false,
            error: null,
            ...initialState
        };
        this.subscribers = [];
    }

    /**
     * Permite a la vista suscribirse a los cambios del estado.
     * @param {Function} callback - Función que se ejecutará al actualizar el estado.
     */
    subscribe(callback) {
        this.subscribers.push(callback);
    }

    /**
     * Notifica a todos los suscriptores enviándoles el estado actualizado.
     */
    notify() {
        this.subscribers.forEach(callback => callback(this.state));
    }

    /**
     * Actualiza el estado actual de la aplicación y notifica los cambios a la vista.
     * @param {Object} newState - Objeto con las propiedades a actualizar.
     */
    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.notify();
    }

    /**
     * Retorna una copia de estado actual.
     */
    getState() {
        return { ...this.state };
    }
}
import { produce } from 'immer';
import { create } from 'zustand';

const useDocsStore = create((set, get) => ({
  docs: [],
  setDocs: docs => set(() => ({ docs: [...docs] })),
  addDocLocally: doc =>
    set(
      produce(state => {
        state.docs = [...state.docs, doc];
      })
    ),
  updateDocLocally: doc =>
    set(
      produce(state => {
        const idx = state.docs.findIndex(d => d.id === doc.id);
        if (idx !== -1) {
          state.docs[idx] = { ...state.docs[idx], ...doc };
        }
      })
    ),
  removeDocLocally: docId =>
    set(
      produce(state => {
        state.docs = state.docs.filter(d => d.id !== docId);
      })
    ),
}));

export default useDocsStore;

import { create } from 'zustand';

const useBoardsStore = create((set, get) => ({
  boards: [],
  activeBoard: null,
  setBoards: boards => set(() => ({ boards: [...boards] })),
  setActiveBoard: board => set(() => ({ activeBoard: board })),
  addBoard: board => set(state => ({ boards: [...state.boards, board] })),
  updateBoard: board =>
    set(state => ({
      boards: state.boards.map(b => (b.id === board.id ? board : b)),
    })),
  removeBoard: boardId =>
    set(state => ({
      boards: state.boards.filter(b => b.id !== boardId),
    })),
}));

export default useBoardsStore;

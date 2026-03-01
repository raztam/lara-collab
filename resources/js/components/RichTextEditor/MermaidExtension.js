import { Node, mergeAttributes } from '@tiptap/core';
import { ReactNodeViewRenderer } from '@tiptap/react';
import MermaidNodeView from './MermaidNodeView';

export const MermaidBlock = Node.create({
  name: 'mermaidBlock',
  group: 'block',
  atom: true,
  draggable: true,

  addAttributes() {
    return {
      code: {
        default: 'graph TD;\n    A --> B',
        parseHTML: element => element.getAttribute('data-code') || element.textContent,
        renderHTML: attributes => ({ 'data-code': attributes.code }),
      },
    };
  },

  parseHTML() {
    return [
      { tag: 'div[data-type="mermaid"]' },
      {
        tag: 'pre',
        getAttrs: node => {
          const code = node.querySelector('code.language-mermaid, code[class*="mermaid"]');
          if (code) {
            return { code: code.textContent };
          }
          return false;
        },
      },
    ];
  },

  renderHTML({ node, HTMLAttributes }) {
    return ['div', mergeAttributes({ 'data-type': 'mermaid' }, HTMLAttributes)];
  },

  addNodeView() {
    return ReactNodeViewRenderer(MermaidNodeView);
  },

  addCommands() {
    return {
      insertMermaidBlock:
        () =>
        ({ commands }) => {
          return commands.insertContent({
            type: this.name,
            attrs: { code: 'graph TD;\n    A --> B' },
          });
        },
    };
  },
});

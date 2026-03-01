import { useComputedColorScheme } from '@mantine/core';
import { RichTextEditor as Editor, Link } from '@mantine/tiptap';
import { Mathematics } from '@tiptap/extension-mathematics';
import Highlight from '@tiptap/extension-highlight';
import Mention from '@tiptap/extension-mention';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import { useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { forwardRef, useImperativeHandle } from 'react';
import 'katex/dist/katex.min.css';
import suggestion from './RichTextEditor/Mention/suggestion.js';
import { MermaidBlock } from './RichTextEditor/MermaidExtension.js';
import classes from './css/RichTextEditor.module.css';
import { IconChartBar } from '@tabler/icons-react';

/**
 * Converts $$...$$ and $...$ text patterns to the BlockMath/InlineMath node
 * format expected by @tiptap/extension-mathematics, for existing content stored as plain text.
 */
function preprocessContent(html) {
  if (!html) {
    return html;
  }
  // Block math first ($$...$$) to avoid conflicting with inline ($...$)
  return html
    .replace(
      /\$\$([\s\S]+?)\$\$/g,
      (_, latex) =>
        `<div data-type="block-math" data-latex="${latex.trim().replace(/"/g, '&quot;')}"></div>`
    )
    .replace(
      /(?<!\$)\$(?!\$)([^\n$]+?)\$(?!\$)/g,
      (_, latex) =>
        `<span data-type="inline-math" data-latex="${latex.trim().replace(/"/g, '&quot;')}"></span>`
    );
}

const RichTextEditor = forwardRef(function RichTextEditor(
  { onChange, placeholder, content, height = 200, readOnly = false, ...props },
  ref
) {
  const editor = useEditor({
    editable: !readOnly,
    extensions: [
      StarterKit,
      Underline,
      Link,
      Highlight,
      Placeholder.configure({ placeholder }),
      Mention.configure({
        HTMLAttributes: {
          class: 'mention',
        },
        suggestion,
      }),
      Mathematics,
      MermaidBlock,
    ],
    content: preprocessContent(content),
    onUpdate({ editor }) {
      onChange(editor.getHTML());
    },
  });

  useImperativeHandle(ref, () => ({
    setContent(content) {
      editor.commands.setContent(preprocessContent(content));
    },
  }));

  const computedColorScheme = useComputedColorScheme();

  return (
    <Editor
      editor={editor}
      {...props}
    >
      <Editor.Toolbar
        sticky
        stickyOffset={60}
      >
        <Editor.ControlsGroup>
          <Editor.Bold />
          <Editor.Italic />
          <Editor.Underline />
          <Editor.Strikethrough />
          <Editor.Highlight />
        </Editor.ControlsGroup>

        <Editor.ControlsGroup>
          <Editor.BulletList />
          <Editor.OrderedList />
        </Editor.ControlsGroup>

        <Editor.ControlsGroup>
          <Editor.Link />
          <Editor.Unlink />
        </Editor.ControlsGroup>

        <Editor.ControlsGroup>
          <Editor.Code />
          <Editor.Blockquote />
        </Editor.ControlsGroup>

        <Editor.ControlsGroup>
          <Editor.Control
            onClick={() => editor?.chain().focus().insertMermaidBlock().run()}
            title='Insert diagram'
            aria-label='Insert diagram'
          >
            <IconChartBar size={16} />
          </Editor.Control>
        </Editor.ControlsGroup>
      </Editor.Toolbar>

      <Editor.Content
        bg={computedColorScheme === 'dark' ? 'dark.6' : 'white'}
        className={classes.content}
        style={{ '--rich-text-editor-height': `${height}px` }}
      />
    </Editor>
  );
});

export default RichTextEditor;

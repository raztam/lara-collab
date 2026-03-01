import { NodeViewWrapper } from '@tiptap/react';
import { ActionIcon, Box, Stack, Textarea, Tooltip } from '@mantine/core';
import { IconEye, IconPencil } from '@tabler/icons-react';
import { useEffect, useRef, useState } from 'react';

let mermaidInstance = null;
let idCounter = 0;

async function getMermaid() {
  if (!mermaidInstance) {
    const mod = await import('mermaid');
    mermaidInstance = mod.default;
    mermaidInstance.initialize({
      startOnLoad: false,
      theme: 'neutral',
      securityLevel: 'loose',
    });
  }
  return mermaidInstance;
}

export default function MermaidNodeView({ node, updateAttributes, editor }) {
  const isNew = node.attrs.code === 'graph TD;\n    A --> B';
  const [isEditing, setIsEditing] = useState(isNew);
  const [code, setCode] = useState(node.attrs.code || '');
  const [svg, setSvg] = useState('');
  const [error, setError] = useState(null);
  const editable = editor.isEditable;
  const idRef = useRef(0);

  useEffect(() => {
    if (!isEditing && code) {
      renderDiagram(code);
    }
  }, [isEditing]);

  useEffect(() => {
    if (!isEditing && node.attrs.code) {
      setCode(node.attrs.code);
      renderDiagram(node.attrs.code);
    }
  }, [node.attrs.code]);

  const renderDiagram = async diagramCode => {
    try {
      const m = await getMermaid();
      const id = `mermaid-${++idRef.current}-${Date.now()}`;
      const { svg: rendered } = await m.render(id, diagramCode);
      setSvg(rendered);
      setError(null);
    } catch (err) {
      setError(String(err));
      setSvg('');
    }
  };

  const handleSave = () => {
    updateAttributes({ code });
    setIsEditing(false);
  };

  return (
    <NodeViewWrapper data-drag-handle>
      {isEditing && editable ? (
        <Stack
          gap='xs'
          p='sm'
          style={{
            border: '1px solid var(--mantine-color-default-border)',
            borderRadius: 8,
          }}
        >
          <Textarea
            value={code}
            onChange={e => setCode(e.target.value)}
            autosize
            minRows={4}
            styles={{ input: { fontFamily: 'monospace', fontSize: 13 } }}
            placeholder='Enter Mermaid diagram code...'
          />
          <ActionIcon
            onClick={handleSave}
            variant='filled'
            size='sm'
            ml='auto'
            title='Preview diagram'
          >
            <IconEye size={14} />
          </ActionIcon>
        </Stack>
      ) : (
        <Box
          style={{ cursor: editable ? 'pointer' : 'default', position: 'relative' }}
          onClick={() => editable && setIsEditing(true)}
        >
          {editable && (
            <Tooltip label='Edit diagram'>
              <ActionIcon
                style={{ position: 'absolute', top: 8, right: 8, zIndex: 1 }}
                variant='subtle'
                size='sm'
              >
                <IconPencil size={14} />
              </ActionIcon>
            </Tooltip>
          )}
          {error ? (
            <Box
              p='sm'
              style={{
                background: 'var(--mantine-color-red-light)',
                borderRadius: 8,
              }}
            >
              <pre
                style={{
                  color: 'var(--mantine-color-red-6)',
                  fontSize: 12,
                  margin: 0,
                  whiteSpace: 'pre-wrap',
                }}
              >
                {error}
              </pre>
            </Box>
          ) : svg ? (
            <Box
              dangerouslySetInnerHTML={{ __html: svg }}
              style={{ display: 'flex', justifyContent: 'center' }}
            />
          ) : (
            <pre style={{ opacity: 0.5 }}>{code}</pre>
          )}
        </Box>
      )}
    </NodeViewWrapper>
  );
}

import BackButton from '@/components/BackButton';
import RichTextEditor from '@/components/RichTextEditor';
import useWebSockets from '@/hooks/useWebSockets';
import MainLayout from '@/layouts/MainLayout';
import { usePage } from '@inertiajs/react';
import { Flex, Stack, TextInput } from '@mantine/core';
import { useDebouncedValue, useDidUpdate } from '@mantine/hooks';
import axios from 'axios';
import { useEffect, useState } from 'react';
import classes from './Show.module.css';

const Show = () => {
  const { project, doc } = usePage().props;
  const [title, setTitle] = useState(doc.title);
  const [content, setContent] = useState(doc.content);
  const [debouncedTitle] = useDebouncedValue(title, 1500);
  const [debouncedContent] = useDebouncedValue(content, 1500);

  const { initProjectWebSocket } = useWebSockets();

  useEffect(() => {
    const cleanup = initProjectWebSocket(project);
    return cleanup;
  }, []);

  useDidUpdate(() => {
    axios.put(route('projects.docs.update', [project.id, doc.id]), {
      title: debouncedTitle,
      content: debouncedContent,
    });
  }, [debouncedTitle, debouncedContent]);

  return (
    <Stack
      p='xl'
      maw={860}
      mx='auto'
    >
      <Flex justify='flex-start'>
        <BackButton
          route='projects.show'
          params={project.id}
        />
      </Flex>
      <TextInput
        variant='unstyled'
        placeholder='Untitled'
        value={title}
        onChange={e => setTitle(e.target.value)}
        classNames={{ input: classes.titleInput }}
      />
      <RichTextEditor
        content={content}
        onChange={setContent}
        height={600}
        placeholder='Start writing...'
      />
    </Stack>
  );
};

Show.layout = page => <MainLayout title={page.props.doc?.title || 'Doc'}>{page}</MainLayout>;

export default Show;

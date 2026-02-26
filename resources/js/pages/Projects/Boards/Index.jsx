import BackButton from '@/components/BackButton';
import useDocsStore from '@/hooks/store/useDocsStore';
import MainLayout from '@/layouts/MainLayout';
import { Link, usePage } from '@inertiajs/react';
import { Button, Center, Divider, Flex, Grid, Stack, Text } from '@mantine/core';
import { IconFile, IconLayoutKanban, IconPlus } from '@tabler/icons-react';
import EmptyWithIcon from '@/components/EmptyWithIcon';
import { useEffect } from 'react';
import BoardCard from './BoardCard';
import CreateBoardModal from './CreateBoardModal';
import CreateDocModal from './CreateDocModal';

const BoardsIndex = () => {
  const { project, boards, docs: initialDocs } = usePage().props;
  const { docs, setDocs } = useDocsStore();

  useEffect(() => {
    setDocs(initialDocs);
  }, [initialDocs]);

  return (
    <>
      <Grid
        justify='space-between'
        align='center'
        mb='xl'
      >
        <Grid.Col span='content'>
          <BackButton route='projects.index' />
        </Grid.Col>
        <Grid.Col span='content'>
          {can('create task group') && (
            <Button
              leftSection={<IconPlus size={14} />}
              radius='xl'
              onClick={CreateBoardModal}
            >
              Add board
            </Button>
          )}
        </Grid.Col>
      </Grid>

      <Text
        fw={600}
        mb='sm'
      >
        Boards
      </Text>

      {boards.length ? (
        <Flex
          gap='lg'
          justify='flex-start'
          align='flex-start'
          direction='row'
          wrap='wrap'
        >
          {boards.map(board => (
            <BoardCard
              key={board.id}
              board={board}
              project={project}
            />
          ))}
        </Flex>
      ) : (
        <Center mih={200}>
          <EmptyWithIcon
            title='No boards found'
            subtitle='Create a board to get started'
            icon={IconLayoutKanban}
          />
        </Center>
      )}

      <Divider my='xl' />

      <Flex
        justify='space-between'
        align='center'
        mb='sm'
      >
        <Text fw={600}>Docs</Text>
        {can('create task group') && (
          <Button
            leftSection={<IconPlus size={14} />}
            radius='xl'
            variant='subtle'
            size='sm'
            onClick={CreateDocModal}
          >
            New doc
          </Button>
        )}
      </Flex>

      {docs.length ? (
        <Stack gap='xs'>
          {docs.map(doc => (
            <Link
              key={doc.id}
              href={route('projects.docs.show', [project.id, doc.id])}
              style={{ textDecoration: 'none' }}
            >
              <Flex
                align='center'
                gap='xs'
                p='sm'
                style={{ borderRadius: 8, cursor: 'pointer' }}
              >
                <IconFile
                  size={16}
                  opacity={0.5}
                />
                <Text size='sm'>{doc.title || 'Untitled'}</Text>
              </Flex>
            </Link>
          ))}
        </Stack>
      ) : (
        <Text
          c='dimmed'
          size='sm'
        >
          No docs yet
        </Text>
      )}
    </>
  );
};

BoardsIndex.layout = page => <MainLayout title={page.props.project?.name}>{page}</MainLayout>;

export default BoardsIndex;

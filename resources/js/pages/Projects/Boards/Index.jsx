import BackButton from '@/components/BackButton';
import MainLayout from '@/layouts/MainLayout';
import { usePage } from '@inertiajs/react';
import { Button, Center, Flex, Grid } from '@mantine/core';
import { IconPlus, IconLayoutKanban } from '@tabler/icons-react';
import EmptyWithIcon from '@/components/EmptyWithIcon';
import BoardCard from './BoardCard';
import CreateBoardModal from './CreateBoardModal';

const BoardsIndex = () => {
  const { project, boards } = usePage().props;

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
        <Center mih={400}>
          <EmptyWithIcon
            title='No boards found'
            subtitle='Create a board to get started'
            icon={IconLayoutKanban}
          />
        </Center>
      )}
    </>
  );
};

BoardsIndex.layout = page => <MainLayout title={page.props.project?.name}>{page}</MainLayout>;

export default BoardsIndex;

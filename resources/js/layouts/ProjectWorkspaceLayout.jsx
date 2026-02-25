import MainLayout from '@/layouts/MainLayout';
import BoardSelector from '@/pages/Projects/Boards/BoardSelector';
import { usePage } from '@inertiajs/react';
import { Divider, Group } from '@mantine/core';

export default function ProjectWorkspaceLayout({ children, title }) {
  const { project, board, boards } = usePage().props;

  return (
    <MainLayout title={title || project?.name}>
      <Group mb='md'>
        <BoardSelector
          project={project}
          boards={boards}
          activeBoard={board}
        />
      </Group>
      {boards && boards.length > 1 && <Divider mb='md' />}
      {children}
    </MainLayout>
  );
}

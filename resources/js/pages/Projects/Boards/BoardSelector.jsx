import { router } from '@inertiajs/react';
import { SegmentedControl } from '@mantine/core';

export default function BoardSelector({ project, boards, activeBoard }) {
  if (!boards || boards.length <= 1) {
    return null;
  }

  const handleChange = value => {
    router.visit(route('projects.boards.show', [project.id, value]));
  };

  return (
    <SegmentedControl
      value={activeBoard.id.toString()}
      onChange={handleChange}
      data={boards.map(b => ({
        value: b.id.toString(),
        label: b.name,
      }))}
      size='sm'
    />
  );
}

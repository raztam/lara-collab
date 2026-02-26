import { redirectTo } from '@/utils/route';
import { Button } from '@mantine/core';
import { IconChevronLeft } from '@tabler/icons-react';

export default function BackButton({ route, params }) {
  return (
    <Button
      leftSection={<IconChevronLeft size={14} />}
      radius='xl'
      variant='transparent'
      size='sm'
      onClick={() => redirectTo(route, params ?? {})}
    >
      Back
    </Button>
  );
}

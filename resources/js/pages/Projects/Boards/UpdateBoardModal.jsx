import useForm from '@/hooks/useForm';
import { Button, Flex, Text, TextInput } from '@mantine/core';
import { modals } from '@mantine/modals';

function ModalForm({ board }) {
  const [form, submit, updateValue] = useForm(
    'put',
    route('projects.boards.update', {
      project: route().params.project,
      board: board.id,
    }),
    { name: board.name },
  );

  const submitModal = event => {
    submit(event, {
      onSuccess: () => modals.closeAll(),
    });
  };

  return (
    <form onSubmit={submitModal}>
      <TextInput
        label='Name'
        placeholder='Board name'
        required
        data-autofocus
        value={form.data.name}
        onChange={e => updateValue('name', e.target.value)}
        error={form.errors.name}
      />

      <Flex
        justify='flex-end'
        mt='xl'
      >
        <Button
          type='submit'
          w={100}
          loading={form.processing}
        >
          Save
        </Button>
      </Flex>
    </form>
  );
}

const UpdateBoardModal = board => {
  modals.open({
    title: (
      <Text
        size='xl'
        fw={700}
        mb={-10}
      >
        Rename board
      </Text>
    ),
    centered: true,
    padding: 'xl',
    overlayProps: { backgroundOpacity: 0.55, blur: 3 },
    children: <ModalForm board={board} />,
  });
};

export default UpdateBoardModal;

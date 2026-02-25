import useForm from '@/hooks/useForm';
import { Button, Flex, Text, TextInput } from '@mantine/core';
import { modals } from '@mantine/modals';

function ModalForm() {
  const [form, submit, updateValue] = useForm(
    'post',
    route('projects.boards.store', route().params.project),
    { name: '' }
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
          Create
        </Button>
      </Flex>
    </form>
  );
}

const CreateBoardModal = () => {
  modals.open({
    title: (
      <Text
        size='xl'
        fw={700}
        mb={-10}
      >
        Create board
      </Text>
    ),
    centered: true,
    padding: 'xl',
    overlayProps: { backgroundOpacity: 0.55, blur: 3 },
    children: <ModalForm />,
  });
};

export default CreateBoardModal;

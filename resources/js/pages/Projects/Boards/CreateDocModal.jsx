import useForm from '@/hooks/useForm';
import { Button, Flex, Text, TextInput } from '@mantine/core';
import { modals } from '@mantine/modals';

function ModalForm() {
  const [form, submit, updateValue] = useForm(
    'post',
    route('projects.docs.store', route().params.project),
    { title: '' }
  );

  const submitModal = event => {
    submit(event, {
      onSuccess: () => modals.closeAll(),
    });
  };

  return (
    <form onSubmit={submitModal}>
      <TextInput
        label='Title'
        placeholder='Doc title'
        required
        data-autofocus
        value={form.data.title}
        onChange={e => updateValue('title', e.target.value)}
        error={form.errors.title}
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

const CreateDocModal = () => {
  modals.open({
    title: (
      <Text
        size='xl'
        fw={700}
        mb={-10}
      >
        Create doc
      </Text>
    ),
    centered: true,
    padding: 'xl',
    overlayProps: { backgroundOpacity: 0.55, blur: 3 },
    children: <ModalForm />,
  });
};

export default CreateDocModal;

import '../global.css'
import '@mantine/core/styles/Paper.css';
import '@mantine/core/styles/Text.css';
import '@mantine/core/styles/Card.css';
import '@mantine/core/styles/Grid.css';
import '@mantine/core/styles/SimpleGrid.css';
import '@mantine/core/styles/Group.css';
import '@mantine/core/styles/Progress.css';
import '@mantine/core/styles/Skeleton.css';
import '@mantine/core/styles/Badge.css';
import '@mantine/core/styles/Table.css';
import '@mantine/core/styles/Popover.css';
import '@mantine/core/styles/Divider.css';
import { MantineProvider, Badge, Text, Progress, Card, Group, Box, SimpleGrid, Skeleton, Paper, Table, HoverCard, Anchor, Divider, rem } from '@mantine/core';
import { IconHelp, IconStar, IconMessage, IconStarFilled, IconHeartRateMonitor, IconBroadcast, IconLockFilled, IconServer, IconSendOff, IconSend, IconHandClick, IconAdjustments } from '@tabler/icons-react';
import { __ } from '@wordpress/i18n';
import useSWR from "swr";
import classes from './reviews.module.css'

const fetcher = ([url,nonce]) => {
  let formData = new FormData();
  formData.append( "action", "cr_get_reviews_top_row_stats" );
  formData.append( "cr_nonce", nonce );
  return fetch(
    url,
    {
      method: "post",
      body: formData,
    }
  ).then(res => res.json());
};

function Reviews({ nonce }) {

  let ratingCard = {
    title: __( "Ratings", "customer-reviews-woocommerce" ),
    count: <Skeleton height={20} width="60%" radius="sm" className={classes.skel}/>,
    descr: __( "Average review rating", "customer-reviews-woocommerce" ),
    channel: __( "Ratings distribution", "customer-reviews-woocommerce" ),
    channelSegm: <Skeleton height={15} width="100%" radius="sm" mt={3} className={classes.skel}/>,
    channelDescr: [
      <Skeleton width="100%" radius="sm" className={classes.skel+" "+classes.skelDescr}><Text fz="xs">...</Text><Text size="xs">...</Text></Skeleton>,
      <Skeleton width="100%" radius="sm" className={classes.skel+" "+classes.skelDescr}><Text fz="xs">...</Text><Text size="xs">...</Text></Skeleton>,
      <Skeleton width="100%" radius="sm" className={classes.skel+" "+classes.skelDescr}><Text fz="xs">...</Text><Text size="xs">...</Text></Skeleton>,
      <Skeleton width="100%" radius="sm" className={classes.skel+" "+classes.skelDescr}><Text fz="xs">...</Text><Text size="xs">...</Text></Skeleton>,
      <Skeleton width="100%" radius="sm" className={classes.skel+" "+classes.skelDescr}><Text fz="xs">...</Text><Text size="xs">...</Text></Skeleton>
    ],
    class: classes.card
  };

  let reviewsCard = {
    title: __( "Reviews", "customer-reviews-woocommerce" ),
    count: <Skeleton height={20} width="60%" radius="sm" className={classes.skel}/>,
    descr: __( "Reviews received", "customer-reviews-woocommerce" ),
    sources: (
      <Box>
        <Skeleton width="100%" height={12} radius="sm" mt="xs" pt={5} pb={10} className={classes.skel}>
          <Text size="xs">...</Text>
        </Skeleton>
        <Skeleton width="100%" height={12} radius="sm" mt="xs" pt={5} pb={10} className={classes.skel}>
          <Text size="xs">...</Text>
        </Skeleton>
      </Box>
    ),
    class: classes.card
  };

  let statusCard = {
    title: __( "Status", "customer-reviews-woocommerce" ),
    class: [classes.card, classes.statusCard],
    reviewReminder: <IconBroadcast className={classes.statusCardBadgeIcon} />,
    reviewRemindersTd1: <Table.Td><Skeleton height={20} width="100%" radius="sm" className={classes.skel}/></Table.Td>,
    reviewRemindersTd2: '',
    reminderSendingTd1: <Table.Td><Skeleton height={20} width="100%" radius="sm" className={classes.skel}/></Table.Td>,
    reminderSendingTd2: ''
  };

  const { data, error, isLoading } = useSWR(
    [ajaxurl,nonce],
    fetcher
  );
  if ( error ) return "An error has occurred.";
  if ( -1 == data ) return "Nonce has expired. Please refresh the page."
  if ( -2 == data ) return "No permissions to view the charts."

  if ( !error && !isLoading ) {
    // ratingCard
    ratingCard.count = <Text className={classes.value}>{data.average}</Text>;
    const segments = data.ratings.map((segment) => (
      <Progress.Section value={segment.part} className={classes[segment.class]} key={segment.label}>
        {segment.part > 10 && <Progress.Label fz="9">{segment.part}%</Progress.Label>}
      </Progress.Section>
    ));
    ratingCard.channelSegm = (
      <Progress.Root size={15} classNames={{ root: classes.progressWithSegments, label: classes.progressLabel }} mt={3} bg="#E1E1E1">
        {segments}
      </Progress.Root>
    );
    ratingCard.channelDescr = data.ratings.map((stat) => (
      <Box key={stat.label} className={classes.stat+' '+classes[stat.class]}>
        <Text tt="uppercase" fz="xs" c="dimmed" fw={700} className={classes.ratingSubTitle}>
          {stat.label}<IconStarFilled size="0.8rem" className={classes.icon} />
        </Text>
        <Group justify="space-between" align="flex-end" className={classes.channelDesc}>
          <Text fw={600} size="xs">{stat.count}</Text>
        </Group>
      </Box>
    ));
    // reviewsCard
    reviewsCard.count = <Text className={classes.value}>{data.total}</Text>;
    reviewsCard.sources = data.sources.map((source, i) => (
      <Box key={source.label} mt="xs" className={classes.progressBox}>
        <Group justify="space-between">
          <Text fz="xs">{source.label}</Text>
          <Text fz="xs">
            {source.part}%
          </Text>
        </Group>
        <Progress value={source.part} mt={5} classNames={{ section: classes[source.class] }} bg="#E1E1E1"/>
      </Box>
    ));
    // statusCard
    let statusCardReviewRemindersIcon = <IconServer className={classes.statusCardBadgeIcon} />;
    let statusCardReviewRemindersGradient = { from: '#7b79e2', to: '#7b79e2', deg: 90 };
    let statusCardReminderSendingIcon = <IconSendOff className={classes.statusCardBadgeIcon} />
    let statusCardReminderSendingGradient = { from: '#da8fcc', to: '#da8fcc', deg: 90 };
    switch (data.status['reviewReminder'].icon) {
      case 'IconBroadcast':
        statusCardReviewRemindersIcon = <IconBroadcast className={classes.statusCardBadgeIcon} />;
        statusCardReviewRemindersGradient = { from: '#7b79e2', to: '#da8fcc', deg: 90 };
        break;
      case 'IconLockFilled':
        statusCardReviewRemindersIcon = <IconLockFilled className={classes.statusCardBadgeIcon} />;
        statusCardReviewRemindersGradient = { from: '#da8fcc', to: '#da8fcc', deg: 90 };
        break;
      default:
        break;
    }
    switch (data.status['reminderSending'].icon) {
      case 'IconSend':
        statusCardReminderSendingIcon = <IconSend className={classes.statusCardBadgeIcon} />
        statusCardReminderSendingGradient = { from: '#7b79e2', to: '#da8fcc', deg: 90 };
        break;
      case 'IconHandClick':
        statusCardReminderSendingIcon = <IconHandClick className={classes.statusCardBadgeIcon} />
        statusCardReminderSendingGradient = { from: '#7b79e2', to: '#7b79e2', deg: 90 };
        break;
      default:
        break;
    }
    statusCard.reviewRemindersTd1 = (
      <Table.Td>
        <Badge
          variant="gradient"
          gradient={statusCardReviewRemindersGradient}
          leftSection={statusCardReviewRemindersIcon}
          size="xs"
          display="flex"
        >
          {data.status['reviewReminder'].label}
        </Badge>
      </Table.Td>
    );
    statusCard.reviewRemindersTd2 = (
      <Table.Td>
        <Group gap="5px">
          <Text fz="xs">
            Review reminders
          </Text>
          <HoverCard width={280} shadow="md" withArrow>
            <HoverCard.Target>
              <IconHelp className={classes.helpIcon} />
            </HoverCard.Target>
            <HoverCard.Dropdown>
              <Text size="xs">
                {data.status['reviewReminder'].help}
              </Text>
              <Divider my="xs" />
              <Group gap="5px">
                <IconAdjustments className={classes.settingsIcon} />
                <Anchor
                  size="xs"
                  href={data.status['reviewReminder'].helpLink}
                >
                  { __( "Review reminders", "customer-reviews-woocommerce" ) }
                </Anchor>
              </Group>
            </HoverCard.Dropdown>
          </HoverCard>
        </Group>
      </Table.Td>
    );
    statusCard.reminderSendingTd1 = (
      <Table.Td>
        <Badge
          variant="gradient"
          gradient={statusCardReminderSendingGradient}
          leftSection={statusCardReminderSendingIcon}
          size="xs"
          display="flex"
          bd="0px"
        >
          {data.status['reminderSending'].label}
        </Badge>
      </Table.Td>
    );
    statusCard.reminderSendingTd2 = (
      <Table.Td>
        <Group gap="5px">
          <Text fz="xs">
            Reminder sending
          </Text>
          <HoverCard width={280} shadow="md" withArrow>
            <HoverCard.Target>
              <IconHelp className={classes.helpIcon} />
            </HoverCard.Target>
            <HoverCard.Dropdown>
              <Text size="xs">
                {data.status['reminderSending'].help}
              </Text>
              <Divider my="xs" />
              <Group gap="5px">
                <IconAdjustments className={classes.settingsIcon} />
                <Anchor
                  size="xs"
                  href={data.status['reminderSending'].helpLink}
                >
                  { __( "Review reminders", "customer-reviews-woocommerce" ) }
                </Anchor>
              </Group>
            </HoverCard.Dropdown>
          </HoverCard>
        </Group>
      </Table.Td>
    );
  }

  return (
    <MantineProvider>
      <SimpleGrid cols={{ base: 1, xs: 3 }} spacing="sm" w="100%" maw="800px" className={classes.topGrid}>
        <Card withBorder padding="xs" className={ratingCard.class}>
          <Group justify="space-between">
            <Text size="xs" c="dimmed" className={classes.title}>
              {ratingCard.title}
            </Text>
            <IconStar size="1.4rem" stroke={1.5} className={classes.icon} />
          </Group>

          <Group align="flex-end" mt={20}>
            {ratingCard.count}
          </Group>

          <Text c="dimmed" fz="xs" mt="7">
            {ratingCard.descr}
          </Text>

          <Text fz="xs" mt="xs">
            {ratingCard.channel}
          </Text>

          {ratingCard.channelSegm}

          <SimpleGrid cols={5} mt="xs">
            {ratingCard.channelDescr}
          </SimpleGrid>
        </Card>
        <Card withBorder padding="xs" className={reviewsCard.class}>
          <Group justify="space-between">
            <Text size="xs" c="dimmed" className={classes.title}>
              {reviewsCard.title}
            </Text>
            <IconMessage size="1.4rem" stroke={1.5} className={classes.icon} />
          </Group>

          <Group align="flex-end" mt={20}>
            {reviewsCard.count}
          </Group>

          <Text c="dimmed" fz="xs" mt="7">
            {reviewsCard.descr}
          </Text>

          {reviewsCard.sources}
        </Card>
        <Card withBorder padding="xs" className={statusCard.class}>
          <Group justify="space-between">
            <Text size="xs" c="dimmed" className={classes.title}>
              {statusCard.title}
            </Text>
            <IconHeartRateMonitor size="1.4rem" stroke={1.5} className={classes.icon} />
          </Group>
          <Table horizontalSpacing="0" verticalSpacing="0" mt={20} withRowBorders={false} className={classes.statusTable}>
            <Table.Tbody>
              <Table.Tr>
                {statusCard.reviewRemindersTd1}
                {statusCard.reviewRemindersTd2}
              </Table.Tr>
              <Table.Tr>
                {statusCard.reminderSendingTd1}
                {statusCard.reminderSendingTd2}
              </Table.Tr>
            </Table.Tbody>
          </Table>
        </Card>
      </SimpleGrid>
    </MantineProvider>
  );
}

export default Reviews

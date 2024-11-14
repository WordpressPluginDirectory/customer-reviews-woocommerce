import '../global.css'
import '@mantine/core/styles/Paper.css';
import '@mantine/core/styles/Text.css';
import '@mantine/core/styles/Card.css';
import '@mantine/core/styles/Grid.css';
import '@mantine/core/styles/SimpleGrid.css';
import '@mantine/core/styles/Group.css';
import '@mantine/core/styles/Progress.css';
import '@mantine/core/styles/Skeleton.css';
import { MantineProvider, Text, Progress, Card, Group, Box, SimpleGrid, Skeleton, Paper, rem } from '@mantine/core';
import { IconHelp, IconStar, IconMessage, IconStarFilled } from '@tabler/icons-react';
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

  const { data, error, isLoading } = useSWR(
    [ajaxurl,nonce],
    fetcher
  );
  if ( error ) return "An error has occurred.";
  if ( -1 == data ) return "Nonce has expired. Please refresh the page."
  if ( -2 == data ) return "No permissions to view the charts."

  if ( !error && !isLoading ) {
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
  }

  return (
    <MantineProvider>
      <SimpleGrid cols={{ base: 1, xs: 2 }} w="100%" maw="600px" className={classes.topGrid}>
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
      </SimpleGrid>
    </MantineProvider>
  );
}

export default Reviews
